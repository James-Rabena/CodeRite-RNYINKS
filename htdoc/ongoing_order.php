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
$res = $conn->query("SELECT o.*,u.firstname,u.lastname,u.email,u.phone 
                   FROM ongoing_orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC");
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Ongoing Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container py-4">
    <h2>Ongoing Orders</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total</th>
                <th>Shipping</th>
                <th>Status</th>
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
                        <td>$<?= number_format($o['total'], 2) ?></td>
                        <td>$<?= number_format($o['shipping_cost'], 2) ?></td>
                        <td>
                            <select class="form-select form-select-sm status-select">
                                <?php foreach (['Processing', 'Shipping'] as $st):
                                    $sel = $o['status'] == $st ? 'selected' : ''; ?>
                                    <option value="<?= $st ?>" <?= $sel ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                        <td>
                            <button class="btn btn-success btn-sm fulfill-btn">Fulfill</button>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="8" class="text-center">No ongoing orders</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="admindashboard.php" class="btn btn-secondary">Back</a>
    <script>
        document.querySelectorAll('.status-select').forEach(sel => {
            sel.addEventListener('change', () => {
                const row = sel.closest('tr'); const id = row.dataset.id;
                fetch('update_ongoing_status.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: sel.value })
                });
            });
        });
        document.querySelectorAll('.fulfill-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr'); const id = row.dataset.id;
                if (!confirm('Mark order ' + id + ' as fulfilled?')) return;
                window.location = 'fulfill_order.php?id=' + id;
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>