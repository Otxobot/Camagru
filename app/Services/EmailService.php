<?php

namespace App\Services;

use App\Models\User;
use App\Models\Image;
use PDO;

class EmailService {
    private $pdo;
    private $userModel;
    private $imageModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->imageModel = new Image($pdo);
    }
    public function sendVerificationEmail($email, $username, $confirmationToken) {
        $verificationLink = "http://localhost:8080/verify-email?token=" . $confirmationToken;
        
        $subject = "Verify your Camagru account";
        $message = $this->getVerificationEmailTemplate($username, $verificationLink);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: camagru18@gmail.com',
            'Reply-To: camagru18@gmail.com',
            'X-Mailer: PHP/' . phpversion()
        ];

        error_clear_last();

        error_log("Attempting to send email to: $email");
        error_log("Subject: $subject");
        error_log("Headers: " . implode("\r\n", $headers));

        $success = mail($email, $subject, $message, implode("\r\n", $headers));
        
        if (!$success) {
            $lastError = error_get_last();
            $errorMessage = $lastError ? $lastError['message'] : 'Unknown mail error';
            error_log("Mail function failed: " . $errorMessage);
            
            // Log additional debugging info
            error_log("PHP mail configuration:");
            error_log("sendmail_path: " . ini_get('sendmail_path'));
            error_log("SMTP: " . ini_get('SMTP'));
            error_log("smtp_port: " . ini_get('smtp_port'));
        } else {
            error_log("Mail function returned success");
        }
        return $success;
    }

    private function getVerificationEmailTemplate($username, $verificationLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Account</title>
        </head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #333; text-align: center;'>Welcome to Camagru!</h2>
                <p>Hello <strong>{$username}</strong>,</p>
                <p>Thank you for signing up for Camagru. To complete your registration, please verify your email address by clicking the link below:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationLink}' 
                       style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Verify My Account
                    </a>
                </div>
                <p>If you can't click the button, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$verificationLink}</p>
                <p><strong>Note:</strong> This verification link will remain valid until you verify your account.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 12px;'>
                    If you didn't create an account with Camagru, please ignore this email.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    public function sendResetPasswordEmail($email, $username, $resetToken) {
        
        $resetLink = "http://localhost:8080/reset-password?token=" . $resetToken;
        
        $subject = "Reset your Camagru password";
        $message = $this->getResetPasswordEmailTemplate($username, $resetLink);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: camagru18@gmail.com',
            'Reply-To: camagru18@gmail.com',
            'X-Mailer: PHP/' . phpversion()
        ];

        $success = mail($email, $subject, $message, implode("\r\n", $headers));
        
        if (!$success) {
            $lastError = error_get_last();
            $errorMessage = $lastError ? $lastError['message'] : 'Unknown mail error';
            error_log("Mail function failed: " . $errorMessage);
        } else {
            error_log("Password reset email sent successfully");
        }
    
        return $success;
    }

    private function getResetPasswordEmailTemplate($username, $resetLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Your Password</title>
        </head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #333; text-align: center;'>Reset Your Password</h2>
                <p>Hello <strong>{$username}</strong>,</p>
                <p>We received a request to reset your password for your Camagru account. If you made this request, click the button below to reset your password:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' 
                       style='background-color: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Reset My Password
                    </a>
                </div>
                <p>If you can't click the button, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$resetLink}</p>
                <p><strong>Important:</strong> This password reset link will expire in 1 hour for security reasons.</p>
                <p><strong>If you didn't request a password reset,</strong> please ignore this email. Your password will remain unchanged.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 12px;'>
                    For security reasons, this link will only work once and expires after 1 hour.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    public function sendImageOwnerEmail($data) {
        $imageId = $data['image_id'];
        $commentContent = $data['comment_content'];
        
        try {
            $image = $this->imageModel->findById($imageId);
            if (!$image) {
                error_log("Image not found for notification: $imageId");
                return false;
            }

            $imageOwner = $this->userModel->findById($image['user_id']);
            if (!$imageOwner) {
                error_log("Image owner not found for notification: {$image['user_id']}");
                return false;
            }

            if (!$this->getUserNotificationPreference($image['user_id'], 'notify_on_comment')) {
                error_log("User {$image['user_id']} has disabled comment notifications");
                return true;
            }

            $commenterId = $_SESSION['user_id'] ?? null;
            if (!$commenterId) {
                error_log("No commenter ID available for notification");
                return false;
            }

            if ($commenterId == $image['user_id']) {
                return true;
            }

            $commenter = $this->userModel->findById($commenterId);
            if (!$commenter) {
                error_log("Commenter not found for notification: $commenterId");
                return false;
            }

            $subject = "New comment on your image - Camagru";
            $message = $this->getImageCommentEmailTemplate(
                $imageOwner['username'],
                $commenter['username'], 
                $commentContent,
                $image['filename']
            );
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: camagru18@gmail.com',
                'Reply-To: camagru18@gmail.com',
                'X-Mailer: PHP/' . phpversion()
            ];

            $success = mail($imageOwner['email'], $subject, $message, implode("\r\n", $headers));
            
            if (!$success) {
                $lastError = error_get_last();
                $errorMessage = $lastError ? $lastError['message'] : 'Unknown mail error';
                error_log("Comment notification email failed: " . $errorMessage);
            } else {
                error_log("Comment notification email sent successfully to {$imageOwner['email']}");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error sending comment notification: " . $e->getMessage());
            return false;
        }
    }

    private function getUserNotificationPreference($userId, $preference) {
        $stmt = $this->pdo->prepare("
            SELECT $preference FROM preferences 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            $this->createDefaultPreferences($userId);
            return true;
        }
        
        return (bool) $result[$preference];
    }

    private function createDefaultPreferences($userId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO preferences (user_id, notify_on_comment) 
            VALUES (:user_id, TRUE)
            ON CONFLICT (user_id) DO NOTHING
        ");
        $stmt->execute([':user_id' => $userId]);
    }

    private function getImageCommentEmailTemplate($ownerUsername, $commenterUsername, $commentContent, $imageFilename) {
        $galleryLink = "http://localhost:8080/gallery";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Comment on Your Image</title>
        </head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #333; text-align: center;'>💬 New Comment on Your Image!</h2>
                <p>Hello <strong>{$ownerUsername}</strong>,</p>
                <p><strong>{$commenterUsername}</strong> left a comment on your image <em>{$imageFilename}</em>:</p>
                <div style='background-color: white; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 0; font-style: italic; color: #555;'>\"{$commentContent}\"</p>
                </div>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$galleryLink}' 
                       style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        View in Gallery
                    </a>
                </div>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                <p style='color: #666; font-size: 12px;'>
                    You're receiving this because someone commented on your image. 
                    You can manage your notification preferences in your profile settings.
                </p>
            </div>
        </body>
        </html>
        ";
    }
}