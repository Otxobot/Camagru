<?php

namespace App\Models;

use PDO;

class Image {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO images (user_id, filename, created_at)
            VALUES (:user_id, :filename, NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':filename' => $data['filename'],
        ]);
        
        return $stmt->fetchColumn();
    }

    public function findAll($limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, u.username, 
                   COUNT(DISTINCT l.id) as likes_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM images i 
            JOIN users u ON i.user_id = u.id 
            LEFT JOIN likes l ON i.id = l.image_id 
            LEFT JOIN comments c ON i.id = c.image_id 
            GROUP BY i.id, u.username 
            ORDER BY i.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, u.username 
            FROM images i 
            JOIN users u ON i.user_id = u.id 
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByUserId($userId, $limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, u.username,
                   COUNT(DISTINCT l.id) as likes_count,
                   COUNT(DISTINCT c.id) as comments_count
            FROM images i 
            JOIN users u ON i.user_id = u.id 
            LEFT JOIN likes l ON i.id = l.image_id 
            LEFT JOIN comments c ON i.id = c.image_id 
            WHERE i.user_id = :user_id 
            GROUP BY i.id, u.username 
            ORDER BY i.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function delete($id, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM images WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function getTotalCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM images");
        return $stmt->fetchColumn();
    }
}