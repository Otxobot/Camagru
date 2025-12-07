<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/Core/Router.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

error_reporting(E_ALL);
ini_set('display_errors', 1);

$router = new Router();

// Routes
$router->get('/', function() {
    include __DIR__ . '/views/home.php';
});

$router->get('/signup', function() {
    include __DIR__ . '/views/signup.php';
});

$router->get('/login', function() {
    include __DIR__ . '/views/login.php';
});

$router->get('/logout', function() {
    include __DIR__ . '/views/logout.php';
});

$router->post('/api/signup', 'AuthController@signup');
$router->post('/api/login', 'AuthController@login');
$router->post('/api/logout', 'AuthController@logout');
$router->get('/verify-email', 'AuthController@verifyEmail');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@resetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');


// API routes for authenticated users
// $router->get('/dashboard', 'AuthController@dashboard');
// $router->post('/api/upload', 'ImageController@upload');
// $router->get('/api/gallery', 'ImageController@gallery');

// Handle the request
try {
    $router->dispatch();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}