<?php
session_start();
require_once 'db_connection.php';

// Check admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: ../login.php');
    exit();
}

$message = "";

// Handle create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_notification'])) {
        $user_id = (int) $_POST['user_id'];
        $product_id = (int) $_POST['product_id'];
        $notif_message = trim($_POST['message']);
        $link = trim($_POST['link']);
        $is_persistent = isset($_POST['is_persistent']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO notifications (user_id, product_id, message, link, is_persistent) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iissi", $user_id, $product_id, $notif_message, $link, $is_persistent);
        $stmt->execute();
        $stmt->close();
        $message = "Notification added.";
    }
    if (isset($_POST['update_notification'])) {
        $id = (int) $_POST['id'];
        $notif_message = trim($_POST['message']);
        $link = trim($_POST['link']);
        $is_persistent = isset($_POST['is_persistent']) ? 1 : 0;
        $stmt = $conn->prepare("UPDATE notifications SET message=?, link=?, is_persistent=? WHERE id=?");
        $stmt->bind_param("ssii", $notif_message, $link, $is_persistent, $id);
        $stmt->execute();
        $stmt->close();
        $message = "Notification updated.";
    }
    if (isset($_POST['delete_notification'])) {
        $id = (int) $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Notification deleted.";
    }
}

// Fetch notifications with user and product info
$sql = "SELECT n.*, u.firstname, u.lastname, p.name AS product_name 
        FROM notifications n
        JOIN users u ON n.user_id=u.id
        JOIN products p ON n.product_id=p.id
        ORDER BY n.created_at DESC";
$notifications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Fetch all users and products for dropdown
$users = $conn->query("SELECT id, CONCAT(firstname,' ',lastname) AS fullname FROM users ORDER BY fullname ASC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5 mb-5">
        <h2>Notifications</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

        <!-- Add Notification Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Send New Notification</h5>
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Persistent?</label><br>
                            <input type="checkbox" name="is_persistent" value="1"> Persistent
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <input type="text" name="message" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link</label>
                        <input type="text" name="link" class="form-control" placeholder="e.g. track_order.php?id=123">
                    </div>
                    <button type="submit" name="add_notification" class="btn btn-primary">Send Notification</button>
                </form>
            </div>
        </div>

        <!-- List Notifications -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Message</th>
                        <th>Link</th>
                        <th>Persistent</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $n): ?>
                        <tr>
                            <form method="post">
                                <td><?= $n['id'] ?><input type="hidden" name="id" value="<?= $n['id'] ?>"></td>
                                <td><?= htmlspecialchars($n['firstname'] . ' ' . $n['lastname']) ?></td>
                                <td><?= htmlspecialchars($n['product_name']) ?></td>
                                <td><input type="text" name="message" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($n['message']) ?>"></td>
                                <td><input type="text" name="link" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($n['link']) ?>"></td>
                                <td><input type="checkbox" name="is_persistent" value="1"
                                        <?= $n['is_persistent'] ? 'checked' : '' ?>></td>
                                <td><?= htmlspecialchars($n['created_at']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-success" name="update_notification">Save</button>
                                    <button class="btn btn-sm btn-danger" name="delete_notification"
                                        onclick="return confirm('Delete notification?')">Delete</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>