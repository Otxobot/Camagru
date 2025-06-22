<?php

// require_once __DIR__ . '/../config/config.php';<
require_once __DIR__ . '/../core/Router.php';

// Autoload classes
// spl_autoload_register(function ($class) {
//     $prefix = 'App\\';
//     $base_dir = __DIR__ . '/../app/';
    
//     $len = strlen($prefix);
//     if (strncmp($prefix, $class, $len) !== 0) {
//         return;
//     }
    
//     $relative_class = substr($class, $len);
//     $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
//     if (file_exists($file)) {
//         require $file;
//     }
// });

$router = new Router();

// Define routes
$router->get('/', function() {
    include __DIR__ . '/views/home.html';
});

$router->get('/signup', function() {
    include __DIR__ . '/views/signup.html';
});

$router->post('/api/signup', 'AuthController@signup');
$router->post('/api/login', 'AuthController@login');
$router->get('/api/logout', 'AuthController@logout');

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