<?php
session_start();
require_once __DIR__ . '/db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in'] || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$shipping_cost = (float) ($_POST['shipping_cost'] ?? 0);
$address = trim($_POST['address'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'GCash'); // Default to GCash

// Generate an 8-character random verification code
$verification_code = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

try {
    $conn->begin_transaction();

    // 1. Fetch cart items (with product name) and calculate totals
    $cart_sql = "
        SELECT ci.product_id, ci.quantity, p.name AS product_name,
               p.price AS original_price, ci.price AS price_paid 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.user_id = ?
    ";
    $stmt_cart = $conn->prepare($cart_sql);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_items = $stmt_cart->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_cart->close();

    if (empty($cart_items)) {
        throw new Exception("Your cart is empty.");
    }

    $total_paid = 0;
    foreach ($cart_items as $item) {
        $total_paid += $item['price_paid'] * $item['quantity'];
    }
    $grand_total = $total_paid + $shipping_cost;

    // 2. Insert a new 'pending' order with the verification code
    $order_sql = "INSERT INTO orders 
        (user_id, total, address, shipping_cost, payment_method, status, verification_code, delivery_status) 
        VALUES (?, ?, ?, ?, ?, 'pending_verification', ?, 'pending_verification')";
    $stmt_order = $conn->prepare($order_sql);
    $stmt_order->bind_param("idssds", $user_id, $grand_total, $address, $shipping_cost, $payment_method, $verification_code);
    $stmt_order->execute();
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // 3. Insert all items into order_items
    $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
    $stmt_item = $conn->prepare($item_sql);
    foreach ($cart_items as $item) {
        $stmt_item->bind_param(
            "iisdi",
            $order_id,
            $item['product_id'],
            $item['product_name'],
            $item['price_paid'],
            $item['quantity']
        );
        $stmt_item->execute();
    }
    $stmt_item->close();

    // 4. Create the persistent notification with the verification code
    $notif_message = "ACTION REQUIRED: Enter code {$verification_code} in the checkout modal to confirm your Order #{$order_id}.";
    $notif_link = "checkout.php?pending_order_id={$order_id}";
    $is_persistent = 1;
    $first_product_id = $cart_items[0]['product_id'];

    $notif_sql = "INSERT INTO notifications (user_id, product_id, message, link, is_persistent) VALUES (?, ?, ?, ?, ?)";
    $stmt_notif = $conn->prepare($notif_sql);
    $stmt_notif->bind_param("iissi", $user_id, $first_product_id, $notif_message, $notif_link, $is_persistent);
    $stmt_notif->execute();
    $stmt_notif->close();

    // 5. Clear the user's cart
    $clear_cart_sql = "DELETE FROM cart_items WHERE user_id = ?";
    $stmt_clear = $conn->prepare($clear_cart_sql);
    $stmt_clear->bind_param("i", $user_id);
    $stmt_clear->execute();
    $stmt_clear->close();

    $conn->commit();

    // 6. Return success
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>