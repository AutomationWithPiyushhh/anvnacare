<?php
// ANVNA Care Database Configuration - alwaysData Production

$host = 'mysql-anvnacare.alwaysdata.net'; 
$port = '3306';
$db   = 'anvnacare_db';                  
$user = 'anvnacare_usr';                 
$pass = 'automation.with.piyush@gmail.com';          // Replace this with your actual database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Output json error if this is an API call, else standard error
     if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
         header('Content-Type: application/json');
         echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
         exit;
     }
     die("Database connection failed: " . $e->getMessage());
}
