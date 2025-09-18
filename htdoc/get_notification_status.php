<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['success' => false, 'count' => 0, 'items' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];


// Get final unread count
$stmt_count = $conn->prepare("SELECT COUNT(id) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$count = $stmt_count->get_result()->fetch_assoc()['unread_count'] ?? 0;
$stmt_count->close();

// *** FIX: Fetch the image_url along with other notification data ***
$stmt_items = $conn->prepare(
    "SELECT n.id ,n.message, n.link, n.is_read, n.created_at, p.image_url 
     FROM notifications n
     JOIN products p ON n.product_id = p.id
     WHERE n.user_id = ? 
     ORDER BY n.created_at DESC LIMIT 5"
);
$stmt_items->bind_param("i", $user_id);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

echo json_encode(['success' => true, 'count' => $count, 'items' => $items]);
$conn->close();