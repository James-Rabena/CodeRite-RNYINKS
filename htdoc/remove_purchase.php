<?php
// filepath: c:\xampp\htdocs\fragrancefusion\remove_purchase.php
session_start();
require_once __DIR__ . '/db_connection.php';

// Check if the user is an admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

// Check if the order ID is provided
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Order ID is required.']);
    exit();
}

$order_id = intval($_POST['order_id']);

// Delete the order from the database
$stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order removed successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to remove the order.']);
}

$stmt->close();
$conn->close();
?>