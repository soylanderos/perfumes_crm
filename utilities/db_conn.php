<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Check if development mode is enabled
$development_mode = filter_var($_ENV['DEVELOPMENT_MODE'], FILTER_VALIDATE_BOOLEAN);

if ($development_mode) {
    // Development Settings
    $dsn = "mysql:host={$_ENV['DEV_DB_HOST']};dbname={$_ENV['DEV_DB_NAME']};";
    $username = $_ENV['DEV_DB_USER'];
    $password = $_ENV['DEV_DB_PASS'];
} else {
    // Production Settings
    $dsn = "mysql:host={$_ENV['PROD_DB_HOST']};dbname={$_ENV['PROD_DB_NAME']};";
    $username = $_ENV['PROD_DB_USER'];
    $password = $_ENV['PROD_DB_PASS'];
    $user_message = $_ENV['PROD_USER_MESSAGE'];
}

