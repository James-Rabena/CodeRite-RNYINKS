<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['superadmin', 'product_admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include 'db_connection.php';
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['id'])) {
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, subcategory=?, stock_quantity=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssdsdiii", $data['name'], $data['description'], $data['price'], $data['category'], $data['subcategory'], $data['stock'], $data['active'], $data['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
$conn->close();
