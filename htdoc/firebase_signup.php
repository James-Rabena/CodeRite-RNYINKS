<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit();
}

$firstname = trim($_POST['firstName'] ?? '');
$lastname = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$role = 'user';

if ($firstname === '' || $lastname === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $_SESSION['error'] = "All fields are required.";
    header('Location: signup.php');
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header('Location: signup.php');
    exit();
}
if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header('Location: signup.php');
    exit();
}
if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: signup.php');
    exit();
}

// check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $_SESSION['error'] = "Email is already registered.";
    $stmt->close();
    header('Location: signup.php');
    exit();
}
$stmt->close();

// insert hashed password locally
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, role) VALUES (?,?,?,?,?)");
$stmt->bind_param("sssss", $firstname, $lastname, $email, $hashedPassword, $role);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "Signup successful. Please check your email for a verification link.";
header('Location: login.php');
exit();
