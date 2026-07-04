<?php
// API - Fetch Health Store Products
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%" . $search . "%";
}

if ($sort === 'price_asc') {
    $query .= " ORDER BY discount_price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY discount_price DESC";
} elseif ($sort === 'rating') {
    $query .= " ORDER BY rating DESC";
} else {
    $query .= " ORDER BY id ASC";
}

$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

try {
    $stmt = $pdo->prepare($query);
    
    $paramIndex = 1;
    foreach ($params as $param) {
        $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($paramIndex++, $param, $type);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Get total count for pagination metadata
    $countQuery = "SELECT COUNT(*) FROM products WHERE 1=1";
    $countParams = [];
    if (!empty($category)) {
        $countQuery .= " AND category = ?";
        $countParams[] = $category;
    }
    if (!empty($search)) {
        $countQuery .= " AND name LIKE ?";
        $countParams[] = "%" . $search . "%";
    }
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalCount = (int)$countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'count' => count($products),
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'products' => $products
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
