<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['superadmin', 'product_admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include 'db_connection.php';
$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
}
$conn->close();
