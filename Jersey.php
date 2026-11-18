<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Jersey.php
 * Handles jersey templates, ready-made jerseys and customization
 * records (FR-02, FR-03, FR-04) + admin catalog management.
 */
class Jersey
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllJerseys(): array
    {
        $stmt = $this->db->query("SELECT * FROM jerseys ORDER BY jersey_id");
        return $stmt->fetchAll();
    }

    /** Only active jerseys visible to customers, filtered by category */
    public function getJerseysByCategory(string $category): array
    {
        $stmt = $this->db->prepare("SELECT * FROM jerseys WHERE category = :cat AND is_active = 1 ORDER BY jersey_id DESC");
        $stmt->execute([':cat' => $category]);
        return $stmt->fetchAll();
    }

    public function getJerseyById(int $jerseyId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM jerseys WHERE jersey_id = :id");
        $stmt->execute([':id' => $jerseyId]);
        $jersey = $stmt->fetch();
        return $jersey ?: null;
    }

    /**
     * Validates and stores an uploaded logo file (used on the customizer).
     */
    public function uploadLogo(array $file): ?string
    {
        return $this->storeImage($file, __DIR__ . '/../uploads/', 'logo_');
    }

    /**
     * Validates and stores an uploaded jersey product photo (admin catalog).
     */
    public function uploadJerseyImage(array $file): ?string
    {
        return $this->storeImage($file, __DIR__ . '/../uploads/jerseys/', 'jersey_');
    }

    private function storeImage(array $file, string $uploadDir, string $prefix): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            return null;
        }

        if ($file['size'] > 3 * 1024 * 1024) { // 3MB limit
            return null;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $prefix . uniqid() . '.' . $ext;
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Return path relative to project root
            $relative = str_replace(__DIR__ . '/../', '', $destination);
            return $relative;
        }
        return null;
    }

    public function saveCustomization(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO customizations
                (user_id, jersey_id, purchase_type, jersey_color, sleeve_color, design_pattern, pattern_color, size, player_name, player_number, logo_path)
             VALUES
                (:user_id, :jersey_id, :purchase_type, :jersey_color, :sleeve_color, :design_pattern, :pattern_color, :size, :player_name, :player_number, :logo_path)"
        );
        $stmt->execute([
            ':user_id'        => $data['user_id'],
            ':jersey_id'      => $data['jersey_id'],
            ':purchase_type'  => $data['purchase_type'] ?? 'Custom',
            ':jersey_color'   => $data['jersey_color'] ?? '#e11d2e',
            ':sleeve_color'   => $data['sleeve_color'] ?? '#FFFFFF',
            ':design_pattern' => $data['design_pattern'] ?? 'Plain',
            ':pattern_color'  => $data['pattern_color'] ?? '#FFD60A',
            ':size'           => $data['size'],
            ':player_name'    => $data['player_name'],
            ':player_number'  => $data['player_number'],
            ':logo_path'      => $data['logo_path'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getCustomizationById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, j.jersey_name, j.base_price, j.image_path AS jersey_image
             FROM customizations c
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE c.customization_id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCustomizationsByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, j.jersey_name, j.base_price, j.image_path AS jersey_image
             FROM customizations c
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             WHERE c.user_id = :user_id
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // ---------------------------------------------------------
    // Admin: catalog management (add / edit / delete jerseys,
    // both Custom Templates and Ready-Made products)
    // ---------------------------------------------------------
    public function addJersey(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO jerseys (jersey_name, category, base_price, description, image_path, base_color, is_active)
             VALUES (:name, :category, :price, :description, :image, :color, :active)"
        );
        $stmt->execute([
            ':name'        => $data['jersey_name'],
            ':category'    => $data['category'],
            ':price'       => $data['base_price'],
            ':description' => $data['description'],
            ':image'       => $data['image_path'],
            ':color'       => $data['base_color'] ?? '#e11d2e',
            ':active'      => $data['is_active'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateJersey(int $id, array $data): bool
    {
        $sql = "UPDATE jerseys SET jersey_name = :name, category = :category, base_price = :price,
                description = :description, base_color = :color, is_active = :active";
        $params = [
            ':name'        => $data['jersey_name'],
            ':category'    => $data['category'],
            ':price'       => $data['base_price'],
            ':description' => $data['description'],
            ':color'       => $data['base_color'] ?? '#e11d2e',
            ':active'      => $data['is_active'] ?? 1,
            ':id'          => $id,
        ];
        if (!empty($data['image_path'])) {
            $sql .= ", image_path = :image";
            $params[':image'] = $data['image_path'];
        }
        $sql .= " WHERE jersey_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteJersey(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM jerseys WHERE jersey_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** All customizations across all users, newest first (for admin) */
    public function getAllCustomizationsWithUser(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, j.jersey_name, j.base_price, j.image_path AS jersey_image,
                    u.username, u.email
             FROM customizations c
             JOIN jerseys j ON c.jersey_id = j.jersey_id
             JOIN users u ON c.user_id = u.user_id
             ORDER BY c.created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
