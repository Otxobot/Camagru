<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\User;
use App\Services\EmailService;

class ProfileController {
    private $userModel;
    private $emailService;

    public function __construct() {
        $pdo = Database::getInstance();
        $this->userModel = new User($pdo);
        $this->emailService = new EmailService($pdo);
    }

    public function updateUsername() {
        header('Content-type: application/json');

        if (!\App\Core\Csrf::validate()) {
            \App\Core\Csrf::reject();
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['current_username']) || !isset($input['new_username'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            if ($input['current_username'] === $input['new_username']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New username must be different from current username']);
                return;
            }

            $existingUser = $this->userModel->findByUsername($input['new_username']);
            if ($existingUser) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username is already taken']);
                return;
            }

            $current_user = $this->userModel->findByUsername($input['current_username']);

            if (!$current_user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Current user not found']);
                return;
            }

            $updateResult = $this->userModel->updateUsername($current_user['id'], $input['new_username']);
            
            if ($updateResult) {
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $current_user['id']) {
                    $_SESSION['username'] = $input['new_username'];
                }

                echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update username']);
            }

        } catch (\Exception $e) {
            error_log('Username update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error updating username']);
        }
    }

    public function updatePassword() {
        header('Content-Type: application/json');

        if (!\App\Core\Csrf::validate()) {
            \App\Core\Csrf::reject();
        }

        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return ;
            }

            if ($input['current_password'] === $input['new_password']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New password must be different from current password']);
                return;
            }

            $current_user = $this->userModel->findById($_SESSION['user_id']);
            if (!$current_user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                return;
            }

            if (!password_verify($input['current_password'], $current_user['password_hash'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                return;
            }

            $complexityCheck = $this->isComplexPassword($input['new_password']);
            if ($complexityCheck !== true) {
                http_response_code(400);
                echo json_encode($complexityCheck);
                return;
            }

            $passwordUpdated = $this->userModel->updatePassword(
                $current_user['id'],
                password_hash($input['new_password'], PASSWORD_DEFAULT)
            );

            if ($passwordUpdated) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Password updated!']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update password']);
            }

        } catch (\Exception $e) {
            error_log('Password update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error updating password']);
        }
    }

    public function updateEmail() {
        header('Content-Type: application/json');

        if (!\App\Core\Csrf::validate()) {
            \App\Core\Csrf::reject();
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);

                    if (!isset($input['current_email']) || !isset($input['new_email'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                        return;
                    }

                    if ($input['current_email'] === $input['new_email']) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'New email must be different from current email']);
                        return;
                    }

                    if (!filter_var($input['new_email'], FILTER_VALIDATE_EMAIL)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                        return;
                    }

                    $existingUser = $this->userModel->findByEmail($input['new_email']);
                    if ($existingUser) {
                        http_response_code(409);
                        echo json_encode(['success' => false, 'message' => 'Email is already taken']);
                        return;
                    }

                    $current_user = $this->userModel->findByEmail($input['current_email']);

                    if (!$current_user) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Current user not found']);
                        return;
                    }

                    $confirmationToken = bin2hex(random_bytes(32));

                    $updateResult = $this->userModel->updateEmail($current_user['id'], $input['new_email'], $confirmationToken);

                    if ($updateResult) {

                        $emailSent = $this->emailService->sendVerificationEmail(
                            $input['new_email'],
                            $current_user['username'],
                            $confirmationToken
                        );

                        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $current_user['id']) {
                            $_SESSION['email'] = $input['new_email'];
                        }

                        if ($emailSent) {
                            echo json_encode([
                                'success' => true, 
                                'message' => 'Email updated successfully! Please check your new email to verify the change.'
                            ]);
                        } else {
                            echo json_encode([
                                'success' => true, 
                                'message' => 'Email updated but verification email could not be sent. Please contact support.'
                            ]);
                        }
                    } else {
                        http_response_code(500);
                        echo json_encode(['success' => false, 'message' => 'Failed to update email']);
                    }

        } catch (\Exception $e) {
            error_log('Email update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error updating email']);
        }
    }

    public function getNotifications() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }

        $prefs = $this->userModel->getNotificationPreferences($_SESSION['user_id']);
        echo json_encode([
            'success' => true,
            'notify_on_comment' => (bool)$prefs['notify_on_comment']
        ]);
    }

    public function updateNotifications() {
        header('Content-Type: application/json');

        if (!\App\Core\Csrf::validate()) {
            \App\Core\Csrf::reject();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $notify = isset($input['notify_on_comment']) ? (bool)$input['notify_on_comment'] : true;

            $result = $this->userModel->updateNotificationPreferences($_SESSION['user_id'], $notify);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification preference updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update preference']);
            }
        } catch (\Exception $e) {
            error_log('Notification update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error updating notification preference']);
        }
    }

    private function isComplexPassword($password) {
        if (strlen($password) < 8) {
            return ['error' => 'Password must be at least 8 characters long'];
        }

        $errors = [];

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'one number';
        }

        if (!empty($errors)) {
            return ['error' => 'Password must contain: ' . implode(', ', $errors) . '.'];
        }

        return true;
    }
}
