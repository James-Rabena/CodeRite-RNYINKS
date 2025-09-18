<?php
session_start();
require_once __DIR__ . '/db_connection.php';

if (empty($_SESSION['user_logged_in']) || empty($_SESSION['pending_order'])) {
    header("Location: checkout.php");
    exit;
}

$pending = $_SESSION['pending_order'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['verification_code'] ?? '');

    if ($code === $pending['verification_code']) {
        // Insert order now
        $stmt = $conn->prepare("INSERT INTO orders 
            (user_id, total, address, shipping_cost, payment_method, verification_code, delivery_status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending_verification')");
        $stmt->bind_param(
            "idssds",
            $pending['user_id'],
            $pending['grand_total'],
            $pending['address'],
            $pending['shipping_cost'],
            $pending['payment'],
            $pending['verification_code']
        );
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        $stmtItems = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($pending['items'] as $item) {
            $stmtItems->bind_param("iisdi", $orderId, $item['product_id'], $item['product_name'], $item['price'], $item['quantity']);
            $stmtItems->execute();
        }
        $stmtItems->close();

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND active=1");
        $stmt->bind_param("i", $pending['user_id']);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['pending_order']);
        $_SESSION['last_order_id'] = $orderId;

        header("Location: order_success.php?order_id={$orderId}");
        exit;
    } else {
        $error = "Invalid verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
            <h3 class="text-center mb-3">Verify Your Order</h3>
            <p class="text-center text-muted">Enter the verification code sent to your email to confirm your order.</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="verification_code" class="form-label">Verification Code</label>
                    <input type="text" class="form-control" id="verification_code" name="verification_code" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Confirm Order</button>
            </form>
        </div>
    </div>
</body>

</html>