<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: admindashboard.php');
    exit();
}

include 'db_connection.php';

// Fetch customers
$customers = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch admins (everything except user)
$admins = $conn->query("SELECT * FROM users WHERE role IN ('admin','superadmin','product_admin','order_admin','support') ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">

    <h2>Manage Users (Superadmin Only)</h2>

    <!-- Customers -->
    <h4>Customer Accounts</h4>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><span
                            class="badge bg-<?= $c['role'] === 'user' ? 'success' : 'secondary' ?>"><?= ucfirst($c['role']) ?></span>
                    </td>
                    <td><?= $c['created_at'] ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="suspend_user.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger">Suspend</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Admins -->
    <h4>Admin Accounts</h4>
    <a href="add_admin.php" class="btn btn-primary mb-2">Add Admin</a>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['firstname'] . ' ' . $a['lastname']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><span class="badge bg-info"><?= $a['role'] ?></span></td>
                    <td><?= $a['created_at'] ?></td>
                    <td>
                        <a href="edit_admin.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_admin.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Delete this admin?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admindashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</body>

</html>
<?php $conn->close(); ?>