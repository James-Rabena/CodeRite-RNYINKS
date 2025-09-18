<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// fetch orders in each stage
function fetchOrders($conn, $table, $user_id)
{
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

$newOrders = fetchOrders($conn, 'orders', $user_id);
$ongoingOrders = fetchOrders($conn, 'ongoing_orders', $user_id);
$fulfilledOrders = fetchOrders($conn, 'fulfilled_orders', $user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">My Orders</h2>

        <!-- New Orders -->
        <h4>Pending / New Orders</h4>
        <?php if (empty($newOrders)): ?>
            <p>You have no new orders.</p>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total</th>
                            <th>Shipping Cost</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                            <th>Created At</th>
                            <th>Address</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']); ?></td>
                                <td>$<?= htmlspecialchars($order['total']); ?></td>
                                <td>$<?= htmlspecialchars($order['shipping_cost']); ?></td>
                                <td><?= htmlspecialchars($order['status']); ?></td>
                                <td><?= htmlspecialchars($order['delivery_status']); ?></td>
                                <td><?= htmlspecialchars($order['created_at']); ?></td>
                                <td><?= htmlspecialchars($order['address']); ?></td>
                                <td><?= htmlspecialchars($order['payment_method']); ?></td>
                                <td><a href="view_order.php?id=<?= (int) $order['id']; ?>&source=orders"
                                        class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Ongoing Orders -->
        <h4>Ongoing Orders</h4>
        <?php if (empty($ongoingOrders)): ?>
            <p>You have no ongoing orders.</p>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total</th>
                            <th>Shipping Cost</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                            <th>Created At</th>
                            <th>Address</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ongoingOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']); ?></td>
                                <td>$<?= htmlspecialchars($order['total']); ?></td>
                                <td>$<?= htmlspecialchars($order['shipping_cost']); ?></td>
                                <td><?= htmlspecialchars($order['status']); ?></td>
                                <td><?= htmlspecialchars($order['delivery_status']); ?></td>
                                <td><?= htmlspecialchars($order['created_at']); ?></td>
                                <td><?= htmlspecialchars($order['address']); ?></td>
                                <td><?= htmlspecialchars($order['payment_method']); ?></td>
                                <td><a href="view_order.php?id=<?= (int) $order['id']; ?>&source=ongoing"
                                        class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Fulfilled Orders -->
        <h4>Fulfilled Orders</h4>
        <?php if (empty($fulfilledOrders)): ?>
            <p>You have no fulfilled orders yet.</p>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total</th>
                            <th>Shipping Cost</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                            <th>Created At</th>
                            <th>Address</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fulfilledOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']); ?></td>
                                <td>$<?= htmlspecialchars($order['total']); ?></td>
                                <td>$<?= htmlspecialchars($order['shipping_cost']); ?></td>
                                <td><?= htmlspecialchars($order['status']); ?></td>
                                <td><?= htmlspecialchars($order['delivery_status']); ?></td>
                                <td><?= htmlspecialchars($order['created_at']); ?></td>
                                <td><?= htmlspecialchars($order['address']); ?></td>
                                <td><?= htmlspecialchars($order['payment_method']); ?></td>
                                <td><a href="view_order.php?id=<?= (int) $order['id']; ?>&source=fulfilled"
                                        class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>
<?php $conn->close(); ?>