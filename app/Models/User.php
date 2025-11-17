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
            INSERT INTO users (username, email, password_hash)
            VALUES (:username, :email, :password)
            RETURNING id
        ");
        
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password']
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
}
