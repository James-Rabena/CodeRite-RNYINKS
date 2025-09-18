<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/includes/PHPMailer/Exception.php';
require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_SESSION['user_logged_in'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address'] ?? '');
    $payment = trim($_POST['billing_method'] ?? '');
    $shipping_cost = (float) ($_POST['shipping_cost'] ?? 0);

    // Fetch cart items
    $stmt = $conn->prepare("SELECT ci.*, p.name AS product_name 
                            FROM cart_items ci 
                            JOIN products p ON ci.product_id = p.id
                            WHERE ci.user_id = ? AND ci.active=1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($cartItems)) {
        die("<h3 class='text-center mt-5'>No active items in your cart.</h3>");
    }

    // Calculate totals
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $grand_total = $total + $shipping_cost;

    // Generate verification code
    $verification_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

    // Save pending order in session (not DB yet)
    $_SESSION['pending_order'] = [
        'user_id' => $user_id,
        'grand_total' => $grand_total,
        'address' => $address,
        'shipping_cost' => $shipping_cost,
        'payment' => $payment,
        'verification_code' => $verification_code,
        'items' => $cartItems
    ];

    // Fetch user email
    $stmt_user = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $userRow = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();
    $user_email = $userRow['email'];

    // Send email with PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rnyinks2323@gmail.com'; // your gmail
        $mail->Password = 'slqv pajd qqts idhb'; // app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('yourgmail@gmail.com', 'RNYINKS Orders');
        $mail->addAddress($user_email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your order at RNYINKS';
        $mail->Body = "Your verification code for your order is: <b>{$verification_code}</b>";

        $mail->send();
        // redirect to verification page
        header("Location: verify_order_mail.php");
        exit;

    } catch (Exception $e) {
        echo "Could not send verification email. Error: {$mail->ErrorInfo}";
    }
}
