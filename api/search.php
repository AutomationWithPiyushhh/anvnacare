<?php
// API - Global Search Auto-suggestion
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$query = trim($_GET['q'] ?? '');

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query too short', 'results' => []]);
    exit;
}

$results = [];
$searchString = "%" . $query . "%";

try {
    // 1. Search Medicines
    $stmt = $pdo->prepare("SELECT id, name FROM medicines WHERE name LIKE ? LIMIT 4");
    $stmt->execute([$searchString]);
    while ($row = $stmt->fetch()) {
        $results[] = [
            'type' => 'medicine',
            'id' => $row['id'],
            'name' => $row['name'],
            'url' => 'medicine-details.php?id=' . $row['id']
        ];
    }

    // 2. Search Products
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE name LIKE ? LIMIT 4");
    $stmt->execute([$searchString]);
    while ($row = $stmt->fetch()) {
        $results[] = [
            'type' => 'product',
            'id' => $row['id'],
            'name' => $row['name'],
            'url' => 'health-store.php?search=' . urlencode($row['name'])
        ];
    }

    // 3. Search Doctors
    $stmt = $pdo->prepare("SELECT id, name, specialization FROM doctors WHERE name LIKE ? OR specialization LIKE ? LIMIT 4");
    $stmt->execute([$searchString, $searchString]);
    while ($row = $stmt->fetch()) {
        $results[] = [
            'type' => 'doctor',
            'id' => $row['id'],
            'name' => $row['name'] . ' (' . $row['specialization'] . ')',
            'url' => 'doctor-details.php?id=' . $row['id']
        ];
    }

    // 4. Search Tests
    $stmt = $pdo->prepare("SELECT id, name FROM tests WHERE name LIKE ? LIMIT 4");
    $stmt->execute([$searchString]);
    while ($row = $stmt->fetch()) {
        $results[] = [
            'type' => 'test',
            'id' => $row['id'],
            'name' => $row['name'],
            'url' => 'lab-tests.php?search=' . urlencode($row['name'])
        ];
    }

    echo json_encode(['success' => true, 'results' => $results]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $e->getMessage(), 'results' => []]);
}
