<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: admindashboard.php');
    exit();
}

include 'db_connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET role = 'suspended' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User suspended successfully.";
    } else {
        $_SESSION['error'] = "Failed to suspend user: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
header("Location: module_users.php");
exit();
