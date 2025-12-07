<?php

namespace App\Models;

use PDO;

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password_hash, confirmation_token)
            VALUES (:username, :email, :password, :confirmation_token)
            RETURNING id
        ");
        
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':confirmation_token' => $data['confirmation_token']
        ]);
        
        return $stmt->fetchColumn();
    }

    public function findByConfirmationToken($token) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users
            WHERE confirmation_token = :token
            AND confirmation_token IS NOT NULL
        ");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    public function confirmEmail($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET is_confirmed = TRUE, confirmation_token = NULL
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $userId]);
    }

    public function storeResetToken($userId, $resetToken) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET reset_token = :reset_token,
                reset_token_expires = :expires
            WHERE id = :user_id
        ");

        $expires = date('Y-m-d H:i:s', time() + 3600);

        $stmt->execute([
            ':reset_token' => $resetToken,
            ':expires' => $expires,
            ':user_id' => $userId
        ]);
    }

    public function findByResetToken($token) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users
            WHERE reset_token = :token
            AND reset_token IS NOT NULL
        ");
        $stmt->execute(["token" => $token]);
        return $stmt->fetch();
    }

    public function isResetTokenExpired($token) {
        $stmt = $this->pdo->prepare("
            SELECT reset_token_expires FROM users
            WHERE reset_token = :token
            AND reset_token IS NOT NULL
        ");
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();

        if (!$result || !$result['reset_token_expires']) {
            return true;
        }

        return strtotime($result['reset_token_expires']) < time();
    }

    public function updatePassword($userId, $hashedPassword) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE id = :user_id
        ");
        return $stmt->execute([
            ':password_hash' => $hashedPassword,
            ':user_id' => $userId
        ]);
    }

    public function clearResetToken($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET reset_token = NULL,
                reset_token_expires = NULL
            WHERE id = :user_id
        ");
        return $stmt->execute([':user_id' => $userId]);
    }
}
