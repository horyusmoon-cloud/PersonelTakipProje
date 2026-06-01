<?php
// config/database.php

$host = 'localhost';
$db_name = 'personel_takip';
$username = 'root'; // Change if necessary for your PHPMyAdmin
$password = '';     // Change if necessary for your PHPMyAdmin

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
    exit;
}
?>
