<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int) ($_GET['id'] ?? 0);

// Fetch the order (belongs to this user)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items with product details
$stmt = $conn->prepare("
    SELECT oi.*, p.name AS product_name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h2>Order #<?php echo $order_id; ?> Details</h2>
        <p>Status: <?php echo htmlspecialchars($order['status']); ?> |
            Delivery: <?php echo htmlspecialchars($order['delivery_status']); ?></p>
        <p>Total: $<?php echo htmlspecialchars($order['total']); ?>
            (Shipping: $<?php echo htmlspecialchars($order['shipping_cost']); ?>)</p>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                style="width:60px;height:60px;object-fit:cover;">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo (int) $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="my_orders.php" class="btn btn-secondary">Back to My Orders</a>
    </div>
</body>

</html>