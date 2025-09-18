<?php
session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])
) {
    header('Location: admindashboard.php');
    exit();
}

require_once 'db_connection.php';
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($order_id <= 0)
    die('Invalid');

$order = $conn->query("SELECT o.*,u.firstname,u.lastname,u.email,u.phone FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=$order_id")->fetch_assoc();
if (!$order)
    die('Order not found');

$items = $conn->query("SELECT * FROM order_items WHERE order_id=$order_id")->fetch_all(MYSQLI_ASSOC);

// generate simple waybill image
$img = imagecreatetruecolor(800, 600);
$white = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefilledrectangle($img, 0, 0, 800, 600, $white);
imagestring($img, 5, 20, 20, "WAYBILL for Order #{$order_id}", $black);
imagestring($img, 4, 20, 50, "Customer: {$order['firstname']} {$order['lastname']}", $black);
imagestring($img, 4, 20, 70, "Email: {$order['email']}  Phone: {$order['phone']}", $black);
imagestring($img, 4, 20, 90, "Address: {$order['address']}", $black);
$y = 120;
$total = 0;
foreach ($items as $it) {
    imagestring($img, 3, 20, $y, "{$it['quantity']}x {$it['product_name']} @ {$it['price']}", $black);
    $total += $it['quantity'] * $it['price'];
    $y += 20;
}
imagestring($img, 4, 20, $y + 20, "Items total: $" . $total . " + Shipping " . $order['shipping_cost'], $black);

// output as png to browser
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);

// move to ongoing_orders
$stmt = $conn->prepare("INSERT INTO ongoing_orders (id,user_id,total,shipping_cost,status,verification_code,delivery_status,created_at,address,payment_method,user_billing_id,payment_type)
 SELECT id,user_id,total,shipping_cost,'Processing',verification_code,delivery_status,created_at,address,payment_method,user_billing_id,payment_type FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$conn->query("DELETE FROM orders WHERE id=$order_id");
?>