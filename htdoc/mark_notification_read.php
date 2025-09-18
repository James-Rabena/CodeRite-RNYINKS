<?php
// mark_notification_read.php
session_start();
require_once 'db_connection.php';
header('Content-Type: application/json');

// 1. Check for authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit;
}

// 2. Check if the required data was sent
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request: notification_id not provided.']);
    exit;
}

$userId = $_SESSION['user_id'];
$notificationId = (int)$_POST['notification_id'];

// 3. Update the database
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND is_read = 0");
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error); // Log error for server admin
    echo json_encode(['success' => false, 'message' => 'Database error preparing statement.']);
    exit;
}

$stmt->bind_param("ii", $notificationId, $userId);

// 4. Send the response
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Notification was already marked as read or did not exist.']);
    }
} else {
    error_log('Execute failed: ' . $stmt->error); // Log error for server admin
    echo json_encode(['success' => false, 'message' => 'Failed to update notification status.']);
}

$stmt->close();
$conn->close();
?>