<?php
// FILE: config/db.php
// Database connection using PDO (safer than mysqli)

$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'twofa_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';


try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,  // Use native prepared statements
        ]
    );
} catch (PDOException $e) {
    // Never expose DB errors to users in production
    error_log('DB Connection failed: ' . $e->getMessage());
    die('Database connection error. Please try again later.');
}
?>
