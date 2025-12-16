<?php

namespace App\Models;

use PDO;

class Like {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function toggle($userId, $imageId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM likes WHERE user_id = :user_id AND image_id = :image_id
        ");
        $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
        $existingLike = $stmt->fetch();

        if ($existingLike) {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes WHERE user_id = :user_id AND image_id = :image_id
            ");
            $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
            return false;
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO likes (user_id, image_id, created_at) 
                VALUES (:user_id, :image_id, NOW())
            ");
            $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
            return true;
        }
    }

    public function isLikedBy($userId, $imageId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM likes WHERE user_id = :user_id AND image_id = :image_id
        ");
        $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getCount($imageId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE image_id = :image_id");
        $stmt->execute([':image_id' => $imageId]);
        return $stmt->fetchColumn();
    }
}