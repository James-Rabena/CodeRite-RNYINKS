<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['user_logged_in'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address'] ?? '');
    $payment = trim($_POST['billing_method'] ?? '');
    $shipping_cost = (float) ($_POST['shipping_cost'] ?? 0);

    // Fetch all active cart items
    $stmt = $conn->prepare("SELECT ci.*, p.name AS product_name 
                            FROM cart_items ci 
                            JOIN products p ON ci.product_id = p.id
                            WHERE ci.user_id = ? AND ci.active=1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($cartItems)) {
        die("<h3 class='text-center mt-5'>No active items in your cart.</h3>");
    }

    // Calculate total
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $grand_total = $total + $shipping_cost;

    // Generate verification code
    $verification_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

    // Store pending order in session
    $_SESSION['pending_order'] = [
        'user_id' => $user_id,
        'address' => $address,
        'payment' => $payment,
        'shipping_cost' => $shipping_cost,
        'grand_total' => $grand_total,
        'verification_code' => $verification_code,
        'cart_items' => $cartItems
    ];

    // Send notification
    $notif_message = "ACTION REQUIRED: Enter code {$verification_code} to confirm your order.";
    $notif_link = "verify_order.php";
    $is_persistent = 1;
    $first_product_id = $cartItems[0]['product_id'];
    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, product_id, message, link, is_persistent) VALUES (?, ?, ?, ?, ?)");
    $stmt_notif->bind_param("iissi", $user_id, $first_product_id, $notif_message, $notif_link, $is_persistent);
    $stmt_notif->execute();
    $stmt_notif->close();

    // Redirect to verify page
    header("Location: verify_order.php");
    exit;
}
?>