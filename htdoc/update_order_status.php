<?php
// update_order_status.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connection.php';

// allow json body or form POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    // fallback to form data
    $data = $_POST;
}

header('Content-Type: application/json');

// authorization: only superadmin or order_admin
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$id = isset($data['id']) ? (int) $data['id'] : 0;
$status = isset($data['status']) ? trim($data['status']) : '';

$allowed = ['pending', 'processing', 'completed', 'cancelled'];
if ($id <= 0 || $status === '' || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param('si', $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close();
