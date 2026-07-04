<?php
// API - Fetch Doctors
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$specialization = $_GET['specialization'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 15);
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];

if (!empty($specialization)) {
    $query .= " AND specialization = ?";
    $params[] = $specialization;
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR specialization LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

if ($sort === 'fee_asc') {
    $query .= " ORDER BY fee ASC";
} elseif ($sort === 'fee_desc') {
    $query .= " ORDER BY fee DESC";
} elseif ($sort === 'experience') {
    $query .= " ORDER BY experience DESC";
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
    $doctors = $stmt->fetchAll();

    // Get total count
    $countQuery = "SELECT COUNT(*) FROM doctors WHERE 1=1";
    $countParams = [];
    if (!empty($specialization)) {
        $countQuery .= " AND specialization = ?";
        $countParams[] = $specialization;
    }
    if (!empty($search)) {
        $countQuery .= " AND (name LIKE ? OR specialization LIKE ?)";
        $countParams[] = "%" . $search . "%";
        $countParams[] = "%" . $search . "%";
    }
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalCount = (int)$countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'count' => count($doctors),
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'doctors' => $doctors
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
