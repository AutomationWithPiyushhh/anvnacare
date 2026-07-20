<?php
// API - Appointments Manager
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to manage appointments.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Parse Inputs
$data = [];
$raw_input = file_get_contents('php://input');
if (!empty($raw_input)) {
    $decoded = json_decode($raw_input, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
$data = array_merge($_POST, $data);

$action = $data['action'] ?? '';

// CSRF protection for all appointment actions
csrf_protect($data);


try {
    if ($action === 'cancel') {
        $appId = (int)($data['appointment_id'] ?? 0);

        if ($appId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
            exit;
        }

        // Verify ownership and status
        $stmt = $pdo->prepare("SELECT id, status FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appId, $userId]);
        $app = $stmt->fetch();

        if (!$app) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
            exit;
        }

        if ($app['status'] !== 'Upcoming') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Only upcoming appointments can be cancelled.']);
            exit;
        }

        // Update status to Cancelled
        $update = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
        $update->execute([$appId]);

        echo json_encode([
            'success' => true,
            'message' => 'Appointment cancelled successfully.'
        ]);
        exit;

    } elseif ($action === 'book') {
        $doctorId = (int)($data['doctor_id'] ?? 0);
        $date = trim($data['date'] ?? '');
        $time = trim($data['time'] ?? '');

        if ($doctorId <= 0 || empty($date) || empty($time)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Doctor, appointment date, and time slot are required.']);
            exit;
        }

        // Verify doctor exists
        $docStmt = $pdo->prepare("SELECT name FROM doctors WHERE id = ?");
        $docStmt->execute([$doctorId]);
        $docName = $docStmt->fetchColumn();

        if (!$docName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Selected doctor not found.']);
            exit;
        }

        // Check if date format is correct (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid date format. Expected YYYY-MM-DD.']);
            exit;
        }

        // Book appointment
        // --- Duplicate slot check: prevent booking same doctor at same date+time ---
        $dupStmt = $pdo->prepare(
            "SELECT id FROM appointments WHERE user_id = ? AND doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status = 'Upcoming'"
        );
        $dupStmt->execute([$userId, $doctorId, $date, $time]);
        if ($dupStmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'You already have an upcoming appointment with this doctor on ' . $date . ' at ' . $time . '.'
            ]);
            exit;
        }
        // --- End duplicate check ---

        $insert = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'Upcoming')");
        $insert->execute([$userId, $doctorId, $date, $time]);
        $appId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Appointment with ' . htmlspecialchars($docName) . ' booked successfully for ' . $date . ' at ' . $time . '.',
            'appointment_id' => $appId
        ]);
        exit;

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
