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
            <?php if ($role === 'superadmin' || $role === 'product_admin'): ?>
                <div class="col-md-4">
                    <a href="module_products.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Products</h5>
                                <p class="card-text">Edit, update, and delete products.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($role === 'superadmin' || $role === 'order_admin'): ?>
                <div class="col-md-4">
                    <a href="module_purchases.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Purchases</h5>
                                <p class="card-text">Track and update purchase orders.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
                <div class="col-md-4">
                    <a href="module_users.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Users</h5>
                                <p class="card-text">Track and update user accounts.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="module_analytics.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Analytics</h5>
                                <p class="card-text">View and analyze sales data.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="module_notifications.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Notifications</h5>
                                <p class="card-text">View and manage user notifications.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="module_contact.php" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Manage Contact Form Submissions</h5>
                                <p class="card-text">View and manage user contact form submissions.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <div class="col-md-4">
                <a href="../index.php" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Go to Website</h5>
                            <p class="card-text">Return to the main site homepage.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>

</html>