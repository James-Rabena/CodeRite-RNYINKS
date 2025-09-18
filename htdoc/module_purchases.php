<?php
session_start();

// if not logged in → go login
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

// block normal users from accessing admin dashboard
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] === 'user') {
    // redirect them to your website homepage (or wherever you like)
    header('Location: ../index.php');
    exit();
}

$role = $_SESSION['user_role']; // superadmin, product_admin, etc.
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .dashboard-card {
            cursor: pointer;
            transition: transform .2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between mb-4">
            <h1>Welcome, <?= htmlspecialchars($adminName) ?></h1>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <div class="row g-4">
            <?php if ($role === 'superadmin' || $role === 'order_admin'): ?>
                <div class="col-md-4">
                    <a href="new_order.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage New Orders</h5>
                                <p class="card-text">Edit, update, and delete products.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($role === 'superadmin' || $role === 'order_admin'): ?>
                <div class="col-md-4">
                    <a href="ongoing_order.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Ongoing Orders</h5>
                                <p class="card-text">Track and update ongoing orders.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($role === 'superadmin' || $role === 'order_admin'): ?>
                <div class="col-md-4">
                    <a href="fulfilled_order.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Fulfilled Orders</h5>
                                <p class="card-text">Track and update fulfilled orders.</p>
                            </div>
                        </div>
                    </a>
                </div>



            <?php endif; ?>

            <div class="col-md-4">
                <a href="admindashbord.php" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Go to Dashboard</h5>
                            <p class="card-text">Return to the dashboard.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>

</html>