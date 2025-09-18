<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications for this user
$stmt = $conn->prepare(
    "SELECT n.id, n.message, n.link, n.is_read, n.created_at, p.image_url
   FROM notifications n
   LEFT JOIN products p ON n.product_id = p.id
   WHERE n.user_id = ?
   ORDER BY n.created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">All Notifications</h2>
        <?php if (empty($notifications)): ?>
            <p>No notifications found.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notif): ?>
                    <a href="<?php echo htmlspecialchars($notif['link']); ?>"
                        class="list-group-item list-group-item-action d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($notif['image_url'] ?? 'assets/product-placeholder.png'); ?>"
                            alt="Product" style="width:50px; height:50px; object-fit:contain; margin-right:15px;">
                        <div>
                            <div style="<?php echo $notif['is_read'] ? '' : 'font-weight:bold;'; ?>">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>
                            <small class="text-muted"><?php echo htmlspecialchars($notif['created_at']); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>