
<?php
$host = '127.0.0.1';
$dbname = 'study_db';
$username = 'root';
$password = 'root';

// Check your actual MySQL port in MAMP settings.
$port =  3306;

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Keep technical details out of the browser.
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check the MAMP configuration.');
}
