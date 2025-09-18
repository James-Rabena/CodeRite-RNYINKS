<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: admindashboard.php');
    exit();
}
include 'db_connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: module_users.php");
    exit();
}
$id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $role, $id);
    if ($stmt->execute()) {
        header("Location: module_users.php");
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}

$admin = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>Edit Admin</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-2">
            <label class="form-label">First Name</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($admin['firstname']) ?>"
                class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Last Name</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($admin['lastname']) ?>" class="form-control"
                required>
        </div>
        <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control"
                required>
        </div>
        <div class="mb-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <?php
                $roles = ['admin', 'product_admin', 'order_admin', 'support', 'superadmin'];
                foreach ($roles as $r) {
                    $sel = $admin['role'] === $r ? "selected" : "";
                    echo "<option value=\"$r\" $sel>$r</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="module_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>

</html>
<?php $conn->close(); ?>