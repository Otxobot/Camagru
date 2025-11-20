<?php

if (!file_exists(__DIR__ . '/../../.env')) {
    die('.env file not found');
}

function loadEnv($filePath) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../../.env');

// Check required environment variables
$required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($required as $var) {
    if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
        die("Required environment variable '$var' is not set or empty");
    }
}


define('DB_HOST', $_ENV['DB_HOST']);
define('DB_PORT', $_ENV['DB_PORT']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);