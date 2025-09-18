<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include 'db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);
$orderId = isset($input['id']) ? (int) $input['id'] : 0;
$status = isset($input['status']) ? trim($input['status']) : '';

$allowed = ['pending', 'processing', 'completed', 'cancelled'];
if ($orderId <= 0 || !in_array($status, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $orderId);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => (bool) $ok]);
$conn->close();
