<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: admindashboard.php');
    exit();
}

require_once 'db_connection.php';

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}

$id = (int) $_GET['id'];

// fetch the user to edit
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $role, $id);
    if ($stmt->execute()) {
        header('Location: manage_users.php');
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>Edit User #<?= htmlspecialchars($user['id']) ?></h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="firstname" class="form-control" value="<?= htmlspecialchars($user['firstname']) ?>"
                required>
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($user['lastname']) ?>"
                required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="product_admin" <?= $user['role'] === 'product_admin' ? 'selected' : ''; ?>>Product Admin
                </option>
                <option value="order_admin" <?= $user['role'] === 'order_admin' ? 'selected' : ''; ?>>Order Admin</option>
                <option value="support" <?= $user['role'] === 'support' ? 'selected' : ''; ?>>Support</option>
                <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>

</html>
<?php $conn->close(); ?>