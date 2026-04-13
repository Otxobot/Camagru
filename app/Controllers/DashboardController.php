<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\Image;
use Exception;

class DashboardController {
    private $imageModel;

    private const STICKERS = [
        1 => 'sunglasses.png',
        2 => 'mustache.png',
        3 => 'hat.png',
        4 => 'bowtie.png',
        5 => 'crown.png',
        6 => 'glasses.png',
    ];

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

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Upload failed: ' . $this->getUploadErrorMessage((int) $file['error'])
                ]);
                return;
            }

            if (empty($file['tmp_name']) || !is_readable($file['tmp_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Uploaded file is not readable']);
                return;
            }

            $stickerFile = $this->getStickerFile((int) $stickerId);
            if (!$stickerFile) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid sticker selection']);
                return;
            }

            $supportsJpeg = (imagetypes() & IMG_JPG) === IMG_JPG;
            $filename = uniqid('photo_') . ($supportsJpeg ? '.jpg' : '.png');
            $filepath = $uploadDir . $filename;
            $stickerPath = __DIR__ . '/../../public/stickers/' . $stickerFile;

            $composeResult = $this->composeImageWithSticker($file['tmp_name'], $stickerPath);
            if (!$composeResult['success']) {
                error_log('Compose photo error: ' . $composeResult['message']);
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $composeResult['message']]);
                return;
            }

            $finalImage = $composeResult['image'];

            $saved = $supportsJpeg
                ? imagejpeg($finalImage, $filepath, 90)
                : imagepng($finalImage, $filepath, 6);

            if (!$saved) {
                imagedestroy($finalImage);
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
                return;
            }

            imagedestroy($finalImage);

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

        } catch (Exception $e) {
            error_log('Save photo error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    private function getStickerFile(int $stickerId): ?string {
        return self::STICKERS[$stickerId] ?? null;
    }

    private function composeImageWithSticker(string $baseImagePath, string $stickerPath) {
        if (!file_exists($baseImagePath)) {
            return ['success' => false, 'message' => 'Base image file not found'];
        }

        $imageInfo = getimagesize($baseImagePath);
        if (!$imageInfo || !isset($imageInfo['mime'])) {
            return ['success' => false, 'message' => 'Invalid uploaded image'];
        }

        $baseImage = $this->createImageResourceFromFile($baseImagePath, $imageInfo['mime']);
        if (!$baseImage) {
            return ['success' => false, 'message' => 'Unsupported image format: ' . $imageInfo['mime']];
        }

        if (!file_exists($stickerPath)) {
            imagedestroy($baseImage);
            return ['success' => false, 'message' => 'Sticker file not found'];
        }

        $stickerImage = imagecreatefrompng($stickerPath);
        if (!$stickerImage) {
            imagedestroy($baseImage);
            return ['success' => false, 'message' => 'Failed to load sticker image'];
        }

        imagesavealpha($stickerImage, true);

        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);
        $stickerWidth = (int) ($baseWidth * 0.28);
        $stickerHeight = (int) (($stickerWidth / imagesx($stickerImage)) * imagesy($stickerImage));

        $x = (int) (($baseWidth - $stickerWidth) / 2);
        $y = (int) ($baseHeight * 0.18);

        $resizedSticker = imagecreatetruecolor($stickerWidth, $stickerHeight);
        imagealphablending($resizedSticker, false);
        imagesavealpha($resizedSticker, true);
        $transparent = imagecolorallocatealpha($resizedSticker, 0, 0, 0, 127);
        imagefill($resizedSticker, 0, 0, $transparent);

        imagecopyresampled(
            $resizedSticker,
            $stickerImage,
            0,
            0,
            0,
            0,
            $stickerWidth,
            $stickerHeight,
            imagesx($stickerImage),
            imagesy($stickerImage)
        );

        imagecopy($baseImage, $resizedSticker, $x, $y, 0, 0, $stickerWidth, $stickerHeight);

        imagedestroy($resizedSticker);
        imagedestroy($stickerImage);

        return ['success' => true, 'image' => $baseImage, 'message' => null];
    }

    private function getUploadErrorMessage(int $errorCode): string {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension',
            default => 'Unknown upload error',
        };
    }

    private function createImageResourceFromFile(string $path, string $mime) {
        if ($mime === 'image/jpeg') {
            if ((imagetypes() & IMG_JPG) !== IMG_JPG) {
                return false;
            }

            return imagecreatefromjpeg($path);
        }

        if ($mime === 'image/png') {
            return imagecreatefrompng($path);
        }

        if ($mime === 'image/gif') {
            return imagecreatefromgif($path);
        }

        return false;
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