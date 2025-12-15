<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\User;
use App\Services\EmailService;

class ProfileController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User(Database::getInstance());
    }

    public function updateUsername() {
        header('Content-type: application/json');

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
                
                session_start();
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $current_user['id']) {
                    $_SESSION['username'] = $input['new_username'];
                }

                echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update username']);
            }

        } catch(Exception $e) {
            error_log('Username update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error updating username']);
        }
    }

    public function updatePassword() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return ;
            }

            if ($input['current_password'] === $input['new_password']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New password must be different from current password']);
            }

            //===============================================
            //=ACUERDATE DE DESCOMENTAR ESTO!!!!!!!!!!!!!!!!=
            //===============================================

            // $complexityCheck = $this->isComplexPassword($input['new_password']);
            // if ($complexityCheck !== true) {
            //     http_response_code(400);
            //     echo json_encode($complexityCheck);
            //     return;
            // }

            $current_user = $this->userModel->findByEmail($input['email']);

            if ($current_user) {
                $passwordUpdated = $this->userModel->updatePassword($current_user['id'], password_hash($input['new_password'], PASSWORD_DEFAULT));
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Password updated!']);
            }

        } catch(Exception $e) {
            error_log('Password update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error updating password']);
        }
    }

    public function updateEmail() {
        header('Content-Type: application/json');

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

                        session_start();
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

        } catch(Exception $e) {
            error_log('Email update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error updating email']);
        }
    }
}
