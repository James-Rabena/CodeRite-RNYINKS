<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'superadmin') {
    header('Location: admindashboard.php');
    exit();
}
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, role) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $firstname, $lastname, $email, $password, $role);
    if ($stmt->execute()) {
        header("Location: module_users.php");
        exit();
    } else {
        $error = "Error adding admin: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>Add New Admin</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-2">
            <label class="form-label">First Name</label>
            <input type="text" name="firstname" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Last Name</label>
            <input type="text" name="lastname" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="product_admin">Product Admin</option>
                <option value="order_admin">Order Admin</option>
                <option value="support">Support</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Admin</button>
        <a href="module_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>

</html>
<?php $conn->close(); ?>