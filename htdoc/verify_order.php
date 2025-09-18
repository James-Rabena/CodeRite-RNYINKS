<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connection.php';

if (empty($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['pending_order'])) {
    die("<h3 class='text-center mt-5'>No pending order found. Please go back to checkout.</h3>");
}

$pending = $_SESSION['pending_order'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code'] ?? '');
    if (strcasecmp($entered_code, $pending['verification_code']) === 0) {
        // Insert order
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

        // Insert items
        $stmtItems = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($pending['cart_items'] as $item) {
            $stmtItems->bind_param("iisdi", $orderId, $item['product_id'], $item['product_name'], $item['price'], $item['quantity']);
            $stmtItems->execute();
        }
        $stmtItems->close();

        // Clear active cart items
        $stmtDel = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND active=1");
        $stmtDel->bind_param("i", $pending['user_id']);
        $stmtDel->execute();
        $stmtDel->close();

        unset($_SESSION['pending_order']);
        $_SESSION['last_order_id'] = $orderId;

        header("Location: order_success.php?order_id={$orderId}");
        exit;
    } else {
        $message = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Order - RNYINKS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: sans-serif;
        }

        .verify-card {
            max-width: 500px;
            margin: 80px auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .verify-header {
            background: #000;
            color: #fff;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }

        .verify-body {
            padding: 30px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="verify-card bg-white">
        <div class="verify-header">
            <h2>Verify Your Order</h2>
            <p class="mb-0">We sent a verification code to your notifications</p>
        </div>
        <div class="verify-body">
            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="verification_code" class="form-label">Verification Code</label>
                    <input type="text" name="verification_code" id="verification_code"
                        class="form-control form-control-lg text-center" placeholder="Enter code" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 btn-lg">Confirm Order</button>
                <a href="checkout.php" class="btn btn-outline-secondary w-100 mt-3">Cancel</a>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>