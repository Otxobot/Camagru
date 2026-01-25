<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Image;

class DashboardController {
    private $imageModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }

        $pdo = Database::getInstance();
        $this->imageModel = new Image($pdo);
    }

    public function index() {
        include __DIR__ . '/../../public/views/dashboard.php';
    }

    public function getPhotos() {
        header('Content-Type: application/json');
        
        try {
            $photos = $this->imageModel->findByUserId($_SESSION['user_id'], 50, 0);
            
            foreach ($photos as &$photo) {
                $photo['file_path'] = '/uploads/' . $photo['filename'];
            }

            echo json_encode([
                'success' => true,
                'photos' => $photos
            ]);

        } catch (Exception $e) {
            error_log('Dashboard photos error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load photos']);
        }
    }

    public function savePhoto() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['image'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No image provided']);
                return;
            }

            $stickerId = $_POST['sticker_id'] ?? null;
            if (!$stickerId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Sticker selection required']);
                return;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $file = $_FILES['image'];
            $filename = uniqid('photo_') . '.jpg';
            $filepath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $imageId = $this->imageModel->create([
                    'user_id' => $_SESSION['user_id'],
                    'filename' => $filename
                ]);

                if ($imageId) {
                    echo json_encode([
                        'success' => true,
                        'image_id' => $imageId,
                        'filename' => $filename
                    ]);
                } else {
                    unlink($filepath); // Remove file if database save failed
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to save to database']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            }

        } catch (Exception $e) {
            error_log('Save photo error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    public function deletePhoto() {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $photoId = $input['photo_id'] ?? null;

            if (!$photoId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Photo ID required']);
                return;
            }

            // Get photo info before deletion
            $photo = $this->imageModel->findById($photoId);
            if (!$photo || $photo['user_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You can only delete your own photos']);
                return;
            }

            // Delete from database
            $deleted = $this->imageModel->delete($photoId, $_SESSION['user_id']);
            
            if ($deleted) {
                // Delete file from filesystem
                $filepath = __DIR__ . '/../../public/uploads/' . $photo['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }

                echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete photo']);
            }

        } catch (Exception $e) {
            error_log('Delete photo error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}