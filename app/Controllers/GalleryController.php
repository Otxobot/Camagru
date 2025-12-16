<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Image;
use App\Models\Like;
use App\Models\Comment;

class GalleryController {
    private $imageModel;
    private $likeModel;
    private $commentModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pdo = Database::getInstance();
        $this->imageModel = new Image($pdo);
        $this->likeModel = new Like($pdo);
        $this->commentModel = new Comment($pdo);
    }

    public function index() {
        include __DIR__ . '/../../public/views/gallery.php';
    }

    public function getImages() {
        header('Content-Type: application/json');
        
        try {
            $page = $_GET['page'] ?? 1;
            $limit = 12;
            $offset = ($page - 1) * $limit;

            $images = $this->imageModel->findAll($limit, $offset);
            $totalImages = $this->imageModel->getTotalCount();

            // Add like status and comments for logged-in users
            if (isset($_SESSION['user_id'])) {
                foreach ($images as &$image) {
                    $image['is_liked'] = $this->likeModel->isLikedBy($_SESSION['user_id'], $image['id']);
                    $image['comments'] = $this->commentModel->findByImageId($image['id']);
                    // Add file path for display (assuming images are stored in uploads folder)
                    $image['file_path'] = '/uploads/' . $image['filename'];
                }
            } else {
                foreach ($images as &$image) {
                    $image['is_liked'] = false;
                    $image['comments'] = $this->commentModel->findByImageId($image['id']);
                    $image['file_path'] = '/uploads/' . $image['filename'];
                }
            }

            echo json_encode([
                'success' => true,
                'images' => $images,
                'pagination' => [
                    'current_page' => (int)$page,
                    'total_pages' => ceil($totalImages / $limit),
                    'total_images' => (int)$totalImages,
                    'has_next' => ($page * $limit) < $totalImages,
                    'has_prev' => $page > 1
                ]
            ]);

        } catch (Exception $e) {
            error_log('Gallery error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load images']);
        }
    }

    public function toggleLike() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Please log in to like images']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $imageId = $input['image_id'] ?? null;

            if (!$imageId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Image ID is required']);
                return;
            }

            // Check if image exists
            $image = $this->imageModel->findById($imageId);
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                return;
            }

            $isLiked = $this->likeModel->toggle($_SESSION['user_id'], $imageId);
            $likeCount = $this->likeModel->getCount($imageId);

            echo json_encode([
                'success' => true,
                'is_liked' => $isLiked,
                'like_count' => (int)$likeCount
            ]);

        } catch (Exception $e) {
            error_log('Like toggle error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to toggle like']);
        }
    }

    public function addComment() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Please log in to comment']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $imageId = $input['image_id'] ?? null;
            $content = trim($input['content'] ?? '');

            if (!$imageId || !$content) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Image ID and comment content are required']);
                return;
            }

            if (strlen($content) > 500) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Comment too long (max 500 characters)']);
                return;
            }

            $image = $this->imageModel->findById($imageId);
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                return;
            }

            $commentId = $this->commentModel->create([
                'user_id' => $_SESSION['user_id'],
                'image_id' => $imageId,
                'content' => $content
            ]);

            $comments = $this->commentModel->findByImageId($imageId);
            $commentCount = $this->commentModel->getCount($imageId);

            echo json_encode([
                'success' => true,
                'comment_id' => $commentId,
                'comments' => $comments,
                'comment_count' => (int)$commentCount
            ]);

        } catch (Exception $e) {
            error_log('Add comment error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
    }

    public function deleteComment() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Please log in']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $commentId = $input['comment_id'] ?? null;

            if (!$commentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
                return;
            }

            $deleted = $this->commentModel->delete($commentId, $_SESSION['user_id']);

            if (!$deleted) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You can only delete your own comments']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);

        } catch (Exception $e) {
            error_log('Delete comment error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
        }
    }
}
