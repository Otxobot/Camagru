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
                echo json_encode(['error' => 'This email is already taken']);
                return;
            }

            if ($this->userModel->findByUsername($input['username'])) {
                http_response_code(409);
                echo json_encode(['error' => 'This username is already taken']);
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
            $input = json_decode(file_get_contents('php://input'), true);
        
            $current_user = $this->userModel->findByEmail($input['email']);
            
            if ($current_user) {
                $resetToken = bin2hex(random_bytes(32));

                $this->userModel->storeResetToken($current_user['id'], $resetToken);

                $emailSent = $this->emailService->sendResetPasswordEmail(
                    $current_user['email'],
                    $current_user['username'],
                    $resetToken
                );
            }

            echo json_encode(['message' => 'If the email exists, a reset link has been sent']);
            
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }

    public function resetPassword() {

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                $token = $_GET['token'] ?? '';

                if (!$token) {
                    $this->renderResetPasswordResult(false, 'Invalid reset link.');
                    return;
                }

                try {
                    $user = $this->userModel->findByResetToken($token);
                    
                    if (!$user) {
                        $this->renderResetPasswordResult(false, 'Invalid or expired reset link.');
                        return;
                    }
                    
                    if ($this->userModel->isResetTokenExpired($token)) {
                        $this->renderResetPasswordResult(false, 'Reset link has expired. Please request a new one.');
                        return;
                    }
                    
                    $this->renderResetPasswordForm($token);
                    
                } catch (Exception $e) {
                    $this->renderResetPasswordResult(false, 'An error occurred. Please try again.');
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $token = $_POST['reset_token'] ?? '';

                if (!$token) {
                    $this->renderResetPasswordResult(false, 'Invalid reset token.');
                    return;
                }

                try {
                    $newPassword = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';
                    
                    if (!$newPassword || !$confirmPassword) {
                        $this->renderResetPasswordResult(false, 'Please fill in all fields.');
                        return;
                    }
                    
                    if ($newPassword !== $confirmPassword) {
                        $this->renderResetPasswordResult(false, 'Passwords do not match.');
                        return;
                    }
                    
                    // Uncomment when ready to enforce password complexity
                    // $complexityCheck = $this->isComplexPassword($newPassword);
                    // if ($complexityCheck !== true) {
                    //     $this->renderResetPasswordResult(false, $complexityCheck['error']);
                    //     return;
                    // }
                    
                    $user = $this->userModel->findByResetToken($token);
                    
                    if (!$user || $this->userModel->isResetTokenExpired($token)) {
                        $this->renderResetPasswordResult(false, 'Invalid or expired reset link.');
                        return;
                    }
                    
                    $passwordUpdated = $this->userModel->updatePassword($user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
                    $tokenCleared = $this->userModel->clearResetToken($user['id']);
                    
                    if ($passwordUpdated && $tokenCleared) {
                        $this->renderResetPasswordResult(true, 'Your password has been reset successfully!');
                    } else {
                        $this->renderResetPasswordResult(false, 'Failed to reset password. Please try again.');
                    }
                    
                } catch (Exception $e) {
                    $this->renderResetPasswordResult(false, 'An error occurred during password reset.');
                }
            }
    }
 
    private function renderResetPasswordForm($token) {
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Password | Camagru</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='/css/styles-home.css'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card'>
                            <div class='card-body'>
                                <h3 class='card-title text-center'>Reset Your Password</h3>
                                <form method='POST'>
                                    <div class='mb-3'>
                                        <label for='password' class='form-label'>New Password</label>
                                        <input type='password' class='form-control' id='password' name='password' required>
                                    </div>
                                    <div class='mb-3'>
                                        <label for='confirm_password' class='form-label'>Confirm New Password</label>
                                        <input type='password' class='form-control' id='confirm_password' name='confirm_password' required>
                                    </div>
                                    <input type='hidden' name='reset_token' value='{$token}'>
                                    <div class='d-grid'>
                                        <button type='submit' class='btn btn-primary'>Reset Password</button>
                                    </div>
                                </form>
                                <div class='text-center mt-3'>
                                    <a href='/' class='btn btn-link'>Back to Home</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function renderResetPasswordResult($success, $message) {
        $status = $success ? 'success' : 'error';
        $title = $success ? 'Password Reset Successful' : 'Password Reset Failed';

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
                                " . ($success ? "<a href='/login' class='btn btn-success ms-2'>Login Now</a>" : "<a href='/forgot-password' class='btn btn-secondary ms-2'>Try Again</a>") . "
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
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
                $_SESSION['email'] = $user['email'];
                $_SESSION['created_at'] = $user['created_at'];
                
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