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
$order_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$allowed = ['id', 'user_id', 'created_at', 'total'];
if (!in_array($order_by, $allowed))
    $order_by = 'id';

$res = $conn->query("SELECT f.*,u.firstname,u.lastname,u.email
                   FROM fulfilled_orders f JOIN users u ON u.id=f.user_id
                   ORDER BY f.$order_by DESC");
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Fulfilled Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container py-4">
    <h2>Fulfilled Orders</h2>
    <form method="get" class="mb-3">
        <label>Sort by:
            <select name="sort" onchange="this.form.submit()" class="form-select form-select-sm d-inline w-auto">
                <?php foreach ($allowed as $c): ?>
                    <option value="<?= $c ?>" <?= $order_by == $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Total</th>
                <th>Shipping</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders):
                foreach ($orders as $o): ?>
                    <tr>
                        <td><?= (int) $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['firstname'] . ' ' . $o['lastname']) ?></td>
                        <td><?= htmlspecialchars($o['email']) ?></td>
                        <td>$<?= number_format($o['total'], 2) ?></td>
                        <td>$<?= number_format($o['shipping_cost'], 2) ?></td>
                        <td><?= htmlspecialchars($o['status']) ?></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="7" class="text-center">No fulfilled orders</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="admindashboard.php" class="btn btn-secondary">Back</a>
</body>

</html>
<?php $conn->close(); ?>