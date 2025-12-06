<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\User;
use App\Services\EmailService;

class AuthController {
    private $userModel;
    private $emailService;

    public function __construct() {
        $this->userModel = new User(Database::getInstance());
        $this->emailService = new EmailService();
    }

    public function signup() {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input['username'] || !$input['email'] || !$input['password']) {
                http_response_code(400);
                echo json_encode(['error' => 'All fields are required']);
                return;
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format']);
                return;
            }

            //===============================================
            //=ACUERDATE DE DESCOMENTAR ESTO!!!!!!!!!!!!!!!!=
            //===============================================

            // $complexityCheck = $this->isComplexPassword($input['password']);
            // if ($complexityCheck !== true) {
            //     http_response_code(400);
            //     echo json_encode($complexityCheck);
            //     return;
            // }

            if ($this->userModel->findByEmail($input['email'])) {
                http_response_code(409);
                echo json_encode(['error' => 'Email already registered']);
                return;
            }

            if ($this->userModel->findByUsername($input['username'])) {
                http_response_code(409);
                echo json_encode(['error' => 'Username already taken']);
                return;
            }

            $confirmationToken = bin2hex(random_bytes(32));

            $userId = $this->userModel->create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => password_hash($input['password'], PASSWORD_DEFAULT),
                'confirmation_token' => $confirmationToken
            ]);

            if ($userId) {
                //Enviando email de verificacion
                $emailSent = $this->emailService->sendVerificationEmail(
                    $input['email'],
                    $input['username'],
                    $confirmationToken
                );

                if ($emailSent) {
                    http_response_code(201);
                    echo json_encode([
                        'message' => 'Account created successfully! Please check your email to verify your account.',
                        'user_id' => $userId
                    ]);
                } else {
                    http_response_code(201);
                    echo json_encode([
                        'message' => 'Account created but email could not be sent. Please contact support.',
                        'user_id' => $userId
                    ]);
                }

            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create user']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }

    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (!$token) {
            $this->renderVerificationResult(false, 'Invalid verification link.');
            return;
        }
        
        try {
            $user = $this->userModel->findByConfirmationToken($token);
            
            if (!$user) {
                $this->renderVerificationResult(false, 'Invalid or expired verification link.');
                return;
            }

            
            if ($user['is_confirmed']) {
                $this->renderVerificationResult(true, 'Your account is already verified!');
                return;
            }
            
            $verified = $this->userModel->confirmEmail($user['id']);
            
            if ($verified) {
                $this->renderVerificationResult(true, 'Your account has been verified successfully!');
            } else {
                $this->renderVerificationResult(false, 'Verification failed. Please try again.');
            }
            
        } catch (Exception $e) {
            $this->renderVerificationResult(false, 'An error occurred during verification.');
        }
    }

    private function renderVerificationResult($success, $message) {
        $status = $success ? 'success' : 'error';
        $title = $success ? 'Verification Successful' : 'Verification Failed';

        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title} | Camagru</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='/css/styles-home.css'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-body text-center'>
                                <h3 class='card-title'>{$title}</h3>
                                <p class='card-text'>{$message}</p>
                                <a href='/' class='btn btn-primary'>Go to Home</a>
                                " . ($success ? "<a href='/login' class='btn btn-success ms-2'>Login</a>" : "") . "
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    public function forgotPassword() {
        header('Content-type: application/json');

        
        try {

            $current_user = $this->getCurrentUser();

            if ($current_user) {
                $resetToken = bin2hex(random_bytes(32));

                $this->userModel->storeResetToken($current_user['id'], $resetToken);

                $emailSent = $this->emailService->sendResetPasswordEmail(
                    $user['email'],
                    $user['username'],
                    $resetToken
                );
            }

            echo json_encode(['message' => 'If the email exists, a reset link has been sent']);
            
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }

    public function login() {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input['email'] || !$input['password']) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password required']);
                return;
            }

            $user = $this->userModel->findByEmail($input['email']);
            
            if ($user && password_verify($input['password'], $user['password_hash'])) {
                //Check if email is confirmed
                if (!$user['is_confirmed']) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Please verify your email address before logging in']);
                    return ;
                }

                // Start session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                echo json_encode([
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(['message' => 'Logged out successfully']);
    }

    public function dashboard() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        // Serve dashboard HTML or redirect
        include __DIR__ . '/../../public/views/dashboard.html';
    }

    private function requireAuth() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);    
            echo json_encode(['error' => 'Authentication required']);
            return false;
        }
        return true;
    }

    private function getCurrentUser() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return $this->userModel->findById($_SESSION['user_id']);
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
            $message = 'Password must contain: ' . implode(', ', $errors) . '.';
            return ['error' => $message];
        }

        return true;
    }
}