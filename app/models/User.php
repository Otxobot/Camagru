<?php
require_once __DIR__ . '/../../config/config.php';

class User {
    public static function exists($email, $username) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
        $stmt->execute([
            ':email' => $email,
            ':username' => $username
        ]);
        return $stmt->fetch() !== false;
    }

    public static function create($username, $email, $passwordHash, $token) {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, confirmation_token)
            VALUES (:username, :email, :password_hash, :token)
        ");
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':token' => $token
        ]);
    }
}
