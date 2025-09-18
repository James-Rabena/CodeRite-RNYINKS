<?php
session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])
) {
    header('Location:admindashboard.php');
    exit();
}

require_once 'db_connection.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0)
    die('Invalid');

$stmt = $conn->prepare("INSERT INTO fulfilled_orders (id,user_id,total,shipping_cost,status,verification_code,delivery_status,created_at,address,payment_method,user_billing_id,payment_type)
 SELECT id,user_id,total,shipping_cost,'fulfilled',verification_code,delivery_status,created_at,address,payment_method,user_billing_id,payment_type FROM ongoing_orders WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$conn->query("DELETE FROM ongoing_orders WHERE id=$id");
header('Location: ongoing_order.php');
exit;
?>