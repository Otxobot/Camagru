<?php

namespace App\Models;

use PDO;

class Comment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (user_id, image_id, content, created_at)
            VALUES (:user_id, :image_id, :content, NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':image_id' => $data['image_id'],
            ':content' => $data['content']
        ]);
        
        return $stmt->fetchColumn();
    }

    public function findByImageId($imageId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.image_id = :image_id 
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([':image_id' => $imageId]);
        return $stmt->fetchAll();
    }

    public function delete($id, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    public function getCount($imageId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM comments WHERE image_id = :image_id");
        $stmt->execute([':image_id' => $imageId]);
        return $stmt->fetchColumn();
    }
}