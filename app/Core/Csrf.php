<?php
namespace App\Core;

class Csrf {
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function getToken(): string {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function validate(): bool {
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $postToken   = $_POST['csrf_token'] ?? '';
        $submitted   = $headerToken !== '' ? $headerToken : $postToken;
        $stored      = $_SESSION['csrf_token'] ?? '';
        // Devuelve true si el token guardado no esta vacio, si el token que han enviado no esta vacio y si son iguales
        return $stored !== '' && $submitted !== '' && hash_equals($stored, $submitted);
    }

    public static function reject(): never {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token']);
        exit;
    }
}
