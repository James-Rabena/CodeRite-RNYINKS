<?php
session_start();
require_once __DIR__ . '/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = intval($_POST['cart_id']);
    $active = intval($_POST['active']);

    $stmt = $conn->prepare("UPDATE cart_items SET active = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $active, $cart_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>