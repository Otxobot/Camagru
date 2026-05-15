<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/Core/Router.php';

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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
\App\Core\Csrf::generateToken();

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

$router->get('/profile', function() {
    include __DIR__ . '/views/profile.php';
});

$router->post('/api/signup', 'AuthController@signup');
$router->post('/api/login', 'AuthController@login');
$router->post('/api/logout', 'AuthController@logout');
$router->get('/verify-email', 'AuthController@verifyEmail');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@resetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->post('/api/profile/update-username', 'ProfileController@updateUsername');
$router->post('/api/profile/update-email', 'ProfileController@updateEmail');
$router->post('/api/profile/update-password', 'ProfileController@updatePassword');
$router->get('/api/profile/notifications', 'ProfileController@getNotifications');
$router->post('/api/profile/update-notifications', 'ProfileController@updateNotifications');

$router->get('/gallery', 'GalleryController@index');
$router->get('/api/gallery', 'GalleryController@getImages');
$router->post('/api/gallery/like', 'GalleryController@toggleLike');
$router->post('/api/gallery/comment', 'GalleryController@addComment');
$router->post('/api/gallery/comment/delete', 'GalleryController@deleteComment');

$router->get('/dashboard', 'DashboardController@index');
$router->get('/api/dashboard/photos', 'DashboardController@getPhotos');
$router->post('/api/dashboard/save-photo', 'DashboardController@savePhoto');
$router->delete('/api/dashboard/delete-photo', 'DashboardController@deletePhoto');

// Handle the request
try {
    $router->dispatch();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}