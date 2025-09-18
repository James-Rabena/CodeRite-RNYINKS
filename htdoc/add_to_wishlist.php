<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['success' => false, 'action' => 'none', 'message' => 'User not logged in.']);
    exit();
}

if (isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int) $_POST['product_id'];

    // 1. Check if the item is already in the wishlist
    $stmt_check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt_check->bind_param("ii", $user_id, $product_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $is_wishlisted = $result->num_rows > 0;
    $stmt_check->close();

    // 2. If it is wishlisted, remove it. Otherwise, add it.
    if ($is_wishlisted) {
        // --- REMOVE LOGIC ---
        $stmt_remove = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt_remove->bind_param("ii", $user_id, $product_id);
        if ($stmt_remove->execute()) {
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            echo json_encode(['success' => false, 'action' => 'none', 'message' => 'Failed to remove.']);
        }
        $stmt_remove->close();
    } else {
        // --- ADD LOGIC ---
        $stmt_add = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt_add->bind_param("ii", $user_id, $product_id);
        if ($stmt_add->execute()) {
            echo json_encode(['success' => true, 'action' => 'added']);
        } else {
            echo json_encode(['success' => false, 'action' => 'none', 'message' => 'Failed to add.']);
        }
        $stmt_add->close();
    }

} else {
    echo json_encode(['success' => false, 'action' => 'none', 'message' => 'No product ID provided.']);
}

$conn->close();