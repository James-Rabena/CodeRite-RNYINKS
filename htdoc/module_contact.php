<?php
session_start();
require_once 'db_connection.php';

if (
    !isset($_SESSION['user_logged_in']) ||
    !$_SESSION['user_logged_in'] ||
    !isset($_SESSION['user_role']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'support'])
) {
    header("Location: login.php");
    exit();
}

// Delete message
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM contact_sent WHERE id=$id");
    header("Location: module_contact_sent.php");
    exit();
}

// Send notification to user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $user_id = (int) $_POST['user_id'];
    $notif_message = trim($_POST['notif_message']);
    $notif_link = trim($_POST['notif_link']);
    $is_persistent = isset($_POST['is_persistent']) ? 1 : 0;

    $sql = "INSERT INTO notifications (user_id, product_id, message, link, is_persistent)
            VALUES (?, NULL, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $user_id, $notif_message, $notif_link, $is_persistent);
    $stmt->execute();
    $stmt->close();

    $success_msg = "Notification sent to user ID $user_id.";
}

// Fetch all contact messages
$result = $conn->query("SELECT c.*, u.email as user_email 
                        FROM contact_sent c 
                        JOIN users u ON c.user_id=u.id 
                        ORDER BY c.created_at DESC");
$contacts = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Contact Messages</h2>
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Sent At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id']); ?></td>
                        <td>
                            <?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']); ?><br>
                            <small><?= htmlspecialchars($c['email']); ?></small>
                        </td>
                        <td><?= htmlspecialchars($c['subject']); ?></td>
                        <td style="max-width:300px;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($c['message'])); ?>
                        </td>
                        <td><?= htmlspecialchars($c['created_at']); ?></td>
                        <td>
                            <a href="?delete=<?= $c['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this message?')">Delete</a>
                            <button type="button" class="btn btn-primary btn-sm mt-1" data-bs-toggle="modal"
                                data-bs-target="#notifModal<?= $c['id']; ?>">Notify User</button>

                            <!-- Notification Modal -->
                            <div class="modal fade" id="notifModal<?= $c['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Send Notification to
                                                    <?= htmlspecialchars($c['firstname']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="user_id" value="<?= $c['user_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Message</label>
                                                    <textarea name="notif_message" class="form-control" rows="4"
                                                        required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Optional Link</label>
                                                    <input type="text" name="notif_link" class="form-control"
                                                        placeholder="e.g. order.php?id=123">
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" name="is_persistent" class="form-check-input"
                                                        id="persistent<?= $c['id']; ?>">
                                                    <label class="form-check-label"
                                                        for="persistent<?= $c['id']; ?>">Persistent
                                                        (non-dismissible)</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="send_notification" class="btn btn-primary">Send
                                                    Notification</button>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal -->

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>