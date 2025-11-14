<?php
session_start();
header('Content-Type: application/json');

// Only allow admin access
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin'){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include DB
require_once '../database/db.php';
$pdo = db_connect();

try {
    // Fetch active sessions
    $stmt = $pdo->prepare("
        SELECT s.id AS session_id, u.id AS user_id, u.username, s.ip_address, s.started_at, s.last_activity
        FROM sessions s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE) OR s.active = 1
        ORDER BY s.last_activity DESC
    ");
    $stmt->execute();
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count
    $count = count($sessions);

    echo json_encode([
        'count' => $count,
        'sessions' => $sessions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch sessions',
        'message' => $e->getMessage()
    ]);
}
