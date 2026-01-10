<?php
$host = '127.0.0.1';
$db   = 'wc2026_tipping';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset"; // Connect to server first, select DB later

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 1. Connect to MySQL Server
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 2. Create Database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    $pdo->exec("USE `$db`");
    
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>