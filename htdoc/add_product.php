<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['superadmin', 'product_admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include 'db_connection.php';

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$category = trim($_POST['category'] ?? '');
$subcategory = trim($_POST['subcategory'] ?? '');
$stock = intval($_POST['stock'] ?? 0);

if ($name && $price >= 0) {
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, subcategory, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $category, $subcategory, $stock);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
$conn->close();
