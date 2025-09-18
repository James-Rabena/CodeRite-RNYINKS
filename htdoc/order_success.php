<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connection.php';

// Get last order ID from session
$orderId = $_SESSION['last_order_id'] ?? null;

// Fetch order and items
$order = null;
$orderItems = [];
if ($orderId) {
    // Get order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get items with product info
    $stmt = $conn->prepare("
        SELECT oi.*, p.name AS product_name, p.image_url, p.price AS original_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orderItems[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-card {
            max-width: 800px;
            margin: 80px auto;
            border-radius: 12px;
        }

        .order-items {
            text-align: left;
        }

        .order-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <?php if (file_exists("header.php"))
        include "header.php"; ?>

    <div class="container">
        <div class="card text-white bg-success shadow success-card text-center">
            <div class="card-body p-4">
                <h2 class="card-title">✅ Order Successfully Placed!</h2>
                <?php if ($orderId): ?>
                    <p class="mt-3">Your order <strong>#<?php echo htmlspecialchars($orderId); ?></strong> has been placed
                        successfully.</p>
                <?php else: ?>
                    <p class="mt-3">Your order has been placed successfully.</p>
                <?php endif; ?>

                <!-- Order summary -->
                <?php if ($order && count($orderItems) > 0): ?>
                    <div class="bg-light text-dark rounded p-3 mt-4 order-items">
                        <h5>Order Summary</h5>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                        Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?>
                                        <?php if ($item['price'] < $item['original_price']): ?>
                                            <span
                                                class="text-decoration-line-through text-muted">$<?php echo number_format($item['original_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span
                                    class="fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <p class="fw-bold text-end">Total: $<?php echo number_format($order['total'], 2); ?></p>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="index.php" class="btn btn-light">Go Back to Homepage</a>
                    <a href="collections.php" class="btn btn-outline-light">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (file_exists("footer.php"))
        include "footer.php"; ?>
</body>

</html>