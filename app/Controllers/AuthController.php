<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User(Database::getInstance());
    }

    public function signup() {
        xdebug_info();
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

            $userId = $this->userModel->create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => password_hash($input['password'], PASSWORD_DEFAULT)
            ]);

            if ($userId) {
                http_response_code(201);
                echo json_encode(['message' => 'User created successfully', 'user_id' => $userId]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create user']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server errorrrrr']);
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
            
            if ($user && password_verify($input['password'], $user['password'])) {
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
}