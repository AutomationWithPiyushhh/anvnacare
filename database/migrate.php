<?php
// ANVNA Care Database Migration Script
// Execute via CLI: php database/migrate.php

$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'root';

try {
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    echo "Connected successfully.\n\n";

    // 1. Run Schema SQL
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found at: $schemaFile");
    }
    echo "Executing schema.sql...\n";
    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "Schema imported successfully.\n\n";

    // 2. Run Dummy Data SQL
    $dataFile = __DIR__ . '/dummy_data.sql';
    if (!file_exists($dataFile)) {
        throw new Exception("Dummy data file not found at: $dataFile");
    }
    echo "Executing dummy_data.sql...\n";
    $dataSql = file_get_contents($dataFile);
    $pdo->exec($dataSql);
    echo "Dummy data seeded successfully.\n\n";

    echo "Database setup completed successfully!\n";

} catch (Exception $e) {
    echo "\nMigration failed: " . $e->getMessage() . "\n";
    exit(1);
}
