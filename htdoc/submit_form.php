<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!$user_id) {
        echo "You must be logged in to submit the form.";
        exit();
    }

    $sql = "INSERT INTO contact_sent (user_id, firstname, lastname, email, subject, message)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $firstname, $lastname, $email, $subject, $message);
    $stmt->execute();
    $stmt->close();

    echo "Message submitted successfully!";
}
?>