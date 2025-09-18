<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

if (isset($_POST['notification_id'])) {
    $user_id = $_SESSION['user_id'];
    $notification_id = (int)$_POST['notification_id'];

    // Delete the specific notification for the logged-in user to prevent misuse
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No notification ID provided.']);
}

$conn->close();