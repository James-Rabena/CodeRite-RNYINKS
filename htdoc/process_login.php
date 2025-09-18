<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT id, firstname, lastname, password, role, profile_picture_path FROM users WHERE email = ?");
    if ($stmt === false) {
        $_SESSION['error'] = 'An error occurred. Please try again later.';
        header('Location: login.php');
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture_path'];

            // Redirect based on role
            switch ($user['role']) {
                case 'superadmin':
                    header('Location: admindashboard.php');
                    break;
                case 'admin':
                    header('Location: admindashboard.php');
                    break;
                case 'product_admin':
                    header('Location: admindashboard.php');
                    break;
                case 'order_admin':
                    header('Location: admindashboard.php');
                    break;
                case 'support':
                    header('Location: admindashboard.php');
                    break;
                default:
                    header('Location: ../index.php');
                    break;
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid password.';
        }
    } else {
        $_SESSION['error'] = 'No account found with that email.';
    }

    $stmt->close();
    $conn->close();

    header('Location: login.php');
    exit();
}

// Block direct GET access
header('Location: login.php');
exit();
