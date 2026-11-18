<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * User.php
 * Handles customer registration, authentication and profile data.
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register(string $username, string $email, string $password, string $phone, string $address): array
    {
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email is already registered.'];
        }
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Username is already taken.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, phone, address)
             VALUES (:username, :email, :password, :phone, :address)"
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':phone'    => $phone,
            ':address'  => $address,
        ]);

        return ['success' => true, 'message' => 'Registration successful. Please log in.'];
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (bool) $stmt->fetch();
    }

    private function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return (bool) $stmt->fetch();
    }
}
