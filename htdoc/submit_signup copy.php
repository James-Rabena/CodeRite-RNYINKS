<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit();
}

$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $_SESSION['error'] = "All fields are required.";
    header("Location: signup.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: signup.php");
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header("Location: signup.php");
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: signup.php");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $_SESSION['error'] = "Email is already registered.";
            $stmt->close();
            header("Location: signup.php");
            exit();
        }
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, 'user')");
    if ($stmt) {
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Account created successfully. Please log in.";
            $stmt->close();
            header("Location: login.php");
            exit();
        }
        $stmt->close();
    }

    $_SESSION['error'] = "Failed to create account.";
    header("Location: signup.php");

} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred. Please try again.";
    if (isset($stmt)) {
        $stmt->close();
    }
    header("Location: signup.php");
}

$conn->close();
?>