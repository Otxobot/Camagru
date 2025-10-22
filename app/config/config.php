<?php
require_once __DIR__ . '/../../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../../.env')) {
    die('.env file not found');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');

try {
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();
} catch (Exception $e) {
    die('Environment variables not properly set: ' . $e->getMessage());
}

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_PORT', $_ENV['DB_PORT']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);