<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Order.php
 * Handles order placement, tracking (FR-05, FR-06) and sales reporting.
 */
class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function placeOrder(int $userId, int $customizationId, int $quantity, float $unitPrice, string $address, string $phone): array
    {
        $totalPrice = $unitPrice * $quantity;
        $orderNumber = 'ORD' . strtoupper(uniqid());

        $stmt = $this->db->prepare(
            "INSERT INTO orders
                (order_number, user_id, customization_id, quantity, total_price, delivery_address, delivery_phone)
             VALUES
                (:order_number, :user_id, :customization_id, :quantity, :total_price, :address, :phone)"
        );
        $stmt->execute([
            ':order_number'     => $orderNumber,
            ':user_id'          => $userId,
            ':customization_id' => $customizationId,
            ':quantity'         => $quantity,
            ':total_price'      => $totalPrice,
            ':address'          => $address,
            ':phone'            => $phone,
        ]);

        return [
            'success'      => true,
            'order_id'     => (int) $this->db->lastInsertId(),
            'order_number' => $orderNumber,
        ];
    }

    public function getOrdersByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, c.player_name, c.player_number, c.jersey_color, c.size, c.purchase_type,
                    j.jersey_name, j.image_path AS jersey_image
             FROM orders o
             JOIN customizations c ON o.customization_id = c.customization_id
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE o.user_id = :user_id
             ORDER BY o.order_date DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getOrderByNumber(string $orderNumber, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, c.player_name, c.player_number, c.jersey_color, c.size, c.purchase_type,
                    j.jersey_name, j.image_path AS jersey_image
             FROM orders o
             JOIN customizations c ON o.customization_id = c.customization_id
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE o.order_number = :order_number AND o.user_id = :user_id"
        );
        $stmt->execute([':order_number' => $orderNumber, ':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ---------------------------------------------------------
    // Admin sales report
    // ---------------------------------------------------------

    /** Revenue + order counts grouped by day for the last N days */
    public function getRevenueByDay(int $days = 14): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE(order_date) AS day, COALESCE(SUM(total_price),0) AS revenue, COUNT(*) AS orders_count
             FROM orders
             WHERE order_status <> 'Cancelled' AND order_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(order_date)
             ORDER BY day ASC"
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Best selling jerseys by revenue */
    public function getTopJerseys(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT j.jersey_name, j.image_path, COUNT(o.order_id) AS orders_count,
                    COALESCE(SUM(o.quantity),0) AS units_sold,
                    COALESCE(SUM(o.total_price),0) AS revenue
             FROM orders o
             JOIN customizations c ON o.customization_id = c.customization_id
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE o.order_status <> 'Cancelled'
             GROUP BY j.jersey_id
             ORDER BY revenue DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Order counts grouped by status (for pie/bar chart) */
    public function getStatusBreakdown(): array
    {
        $stmt = $this->db->query("SELECT order_status, COUNT(*) AS total FROM orders GROUP BY order_status");
        return $stmt->fetchAll();
    }

    /** Revenue split between Custom designs and Ready-Made purchases */
    public function getRevenueByPurchaseType(): array
    {
        $stmt = $this->db->query(
            "SELECT c.purchase_type, COALESCE(SUM(o.total_price),0) AS revenue, COUNT(*) AS orders_count
             FROM orders o
             JOIN customizations c ON o.customization_id = c.customization_id
             WHERE o.order_status <> 'Cancelled'
             GROUP BY c.purchase_type"
        );
        return $stmt->fetchAll();
    }

    public function getReportSummary(): array
    {
        $totalOrders = $this->db->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
        $revenue     = $this->db->query("SELECT COALESCE(SUM(total_price),0) r FROM orders WHERE order_status <> 'Cancelled'")->fetch()['r'];
        $avgOrder    = $this->db->query("SELECT COALESCE(AVG(total_price),0) a FROM orders WHERE order_status <> 'Cancelled'")->fetch()['a'];
        $unitsSold   = $this->db->query("SELECT COALESCE(SUM(quantity),0) q FROM orders WHERE order_status <> 'Cancelled'")->fetch()['q'];

        return [
            'total_orders' => (int)$totalOrders,
            'revenue'      => (float)$revenue,
            'avg_order'    => (float)$avgOrder,
            'units_sold'   => (int)$unitsSold,
        ];
    }
}
