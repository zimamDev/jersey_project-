<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Admin.php
 * Handles admin authentication, order administration, user
 * management and dashboard statistics.
 */
class Admin
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login(string $username, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            return ['success' => true, 'admin' => $admin];
        }
        return ['success' => false, 'message' => 'Invalid admin credentials.'];
    }

    public function getAllOrders(): array
    {
        $stmt = $this->db->query(
            "SELECT o.*, u.username, u.email, c.player_name, c.player_number,
                    c.jersey_color, c.size, c.purchase_type, j.jersey_name, j.image_path AS jersey_image
             FROM orders o
             JOIN users u ON o.user_id = u.user_id
             JOIN customizations c ON o.customization_id = c.customization_id
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             ORDER BY o.order_date DESC"
        );
        return $stmt->fetchAll();
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE orders SET order_status = :status WHERE order_id = :id");
        return $stmt->execute([':status' => $status, ':id' => $orderId]);
    }

    public function getDashboardStats(): array
    {
        $totalOrders = $this->db->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
        $pending     = $this->db->query("SELECT COUNT(*) c FROM orders WHERE order_status='Pending'")->fetch()['c'];
        $completed   = $this->db->query("SELECT COUNT(*) c FROM orders WHERE order_status='Completed'")->fetch()['c'];
        $totalUsers  = $this->db->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
        $revenue     = $this->db->query("SELECT COALESCE(SUM(total_price),0) r FROM orders WHERE order_status <> 'Cancelled'")->fetch()['r'];
        $totalJerseys = $this->db->query("SELECT COUNT(*) c FROM jerseys")->fetch()['c'];
        $readymade    = $this->db->query("SELECT COUNT(*) c FROM jerseys WHERE category='Ready-Made'")->fetch()['c'];

        return [
            'total_orders'  => $totalOrders,
            'pending'       => $pending,
            'completed'     => $completed,
            'total_users'   => $totalUsers,
            'revenue'       => $revenue,
            'total_jerseys' => $totalJerseys,
            'readymade'     => $readymade,
        ];
    }

    // ---------------------------------------------------------
    // Admin: customer / user management
    // ---------------------------------------------------------

    /** All registered customers with their order count & total spend */
    public function getAllUsersWithStats(): array
    {
        $stmt = $this->db->query(
            "SELECT u.*,
                    COUNT(DISTINCT o.order_id) AS orders_count,
                    COALESCE(SUM(CASE WHEN o.order_status <> 'Cancelled' THEN o.total_price ELSE 0 END),0) AS total_spent,
                    COUNT(DISTINCT c.customization_id) AS jerseys_count
             FROM users u
             LEFT JOIN orders o ON o.user_id = u.user_id
             LEFT JOIN customizations c ON c.user_id = u.user_id
             GROUP BY u.user_id
             ORDER BY u.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Every jersey design a specific customer has created/bought */
    public function getUserJerseys(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, j.jersey_name, j.image_path AS jersey_image, j.base_price
             FROM customizations c
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE c.user_id = :id
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchAll();
    }

    /** Every order placed by a specific customer */
    public function getUserOrders(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, c.player_name, c.player_number, c.size, j.jersey_name
             FROM orders o
             JOIN customizations c ON o.customization_id = c.customization_id
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE o.user_id = :id
             ORDER BY o.order_date DESC"
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchAll();
    }
}
