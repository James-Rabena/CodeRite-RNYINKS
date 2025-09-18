<?php
// new_order.php
session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])
) {
    header('Location: admindashboard.php');
    exit();
}
require_once 'db_connection.php';

// get all orders
$sql = "SELECT o.id,o.user_id,o.total,o.shipping_cost,o.address,
               u.firstname,u.lastname,u.email,u.phone,
               o.created_at
        FROM orders o
        JOIN users u ON u.id=o.user_id
        ORDER BY o.created_at DESC";
$res = $conn->query($sql);
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>New Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container py-4">
    <h2>New Orders</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Total</th>
                <th>Shipping</th>
                <th>Address</th>
                <th>Placed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders):
                foreach ($orders as $o): ?>
                    <tr data-id="<?= (int) $o['id'] ?>">
                        <td><?= (int) $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['firstname'] . ' ' . $o['lastname']) ?></td>
                        <td><?= htmlspecialchars($o['email']) ?></td>
                        <td><?= htmlspecialchars($o['phone']) ?></td>
                        <td>$<?= number_format($o['total'], 2) ?></td>
                        <td>$<?= number_format($o['shipping_cost'], 2) ?></td>
                        <td><?= htmlspecialchars($o['address']) ?></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                        <td>
                            <a href="view_order.php?id=<?= (int) $o['id'] ?>" class="btn btn-info btn-sm">View</a>
                            <button class="btn btn-primary btn-sm print-waybill-btn">Print Waybill</button>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="9" class="text-center">No orders</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="admindashboard.php" class="btn btn-secondary">Back</a>
    <script>
        document.querySelectorAll('.print-waybill-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const id = row.getAttribute('data-id');
                if (!confirm('Generate waybill for order ' + id + '?')) return;
                window.location = 'print_waybill.php?id=' + id;
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>