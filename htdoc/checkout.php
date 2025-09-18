<?php
session_start();
require_once __DIR__ . '/db_connection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect if not logged in
if (empty($_SESSION['user_logged_in'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}
$user_id = $_SESSION['user_id'];

// User profile
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// Billing methods
$stmt_billing = $conn->prepare("SELECT * FROM user_billing WHERE user_id = ? ORDER BY created_at DESC");
$stmt_billing->bind_param("i", $user_id);
$stmt_billing->execute();
$billing_methods = $stmt_billing->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_billing->close();

// Fetch all active cart items
$sql = "SELECT ci.product_id, ci.quantity, ci.price AS price_paid,
               p.name AS product_name, p.image_url, p.price AS original_price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND ci.active='1'";
$stmt_cart = $conn->prepare($sql);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result = $stmt_cart->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt_cart->close();

// Redirect if no active items
if (empty($cartItems)) {
    die("<h3 class='text-center mt-5'>No active items in your cart. Please add items to checkout.</h3>");
}

// Calculate totals
$subtotal = 0.0;
$final_total = 0.0;
foreach ($cartItems as $item) {
    $subtotal += $item['original_price'] * $item['quantity'];
    $final_total += $item['price_paid'] * $item['quantity'];
}
$total_savings = $subtotal - $final_total;

// Shipping options
$shipping_options = [
    '5.00' => 'Standard Shipping ($5.00) (Reliable, Tracked)',
    '10.00' => 'Express Post ($10.00) (Faster, Insured)',
    '15.00' => 'Priority Express ($15.00) (Fastest, Priority Handling)',
    '20.00' => 'Premium Courier ($20.00) (Fastest, Insured, Direct Contact)'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RNYINKS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .order-summary-card {
            background: #f8f9fa;
            border-radius: 8px;
        }

        .summary-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item-price-details {
            font-size: 0.9em;
            color: #6c757d;
        }

        .original-price-summary {
            text-decoration: line-through;
            margin-left: 8px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container my-5">
        <h2 class="text-center mb-4">Checkout</h2>
        <form id="checkout-form" action="checkout_api.php" method="POST">
            <div class="row">
                <div class="col-lg-7">
                    <!-- Shipping -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-truck me-2"></i>Shipping Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"
                                    required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="shipping_method" class="form-label">Shipping Method</label>
                                <select class="form-select" id="shipping_method" name="shipping_cost" required>
                                    <option value="" selected disabled>Select a shipping option...</option>
                                    <?php foreach ($shipping_options as $price => $desc): ?>
                                        <option value="<?php echo $price; ?>"><?php echo $desc; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-credit-card me-2"></i>Billing Details</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($billing_methods)): ?>
                                <div class="mb-3">
                                    <label for="billing_method" class="form-label">Choose a billing method</label>
                                    <select class="form-select" id="billing_method" name="billing_method" required>
                                        <?php foreach ($billing_methods as $method): ?>
                                            <option value="<?php echo htmlspecialchars($method['provider']); ?>">
                                                <?php
                                                echo htmlspecialchars($method['provider']);
                                                if (!empty($method['card_brand']) && !empty($method['card_last_four'])) {
                                                    echo " - " . htmlspecialchars($method['card_brand']) . " ****" . htmlspecialchars($method['card_last_four']);
                                                } else if (!empty($method['customer_id'])) {
                                                    echo " - " . htmlspecialchars($method['customer_id']);
                                                }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="COD">Cash on Delivery (Pay on arrival)</option>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    No payment methods found. You can select Cash on Delivery below or <a
                                        href="profile.php">add a billing method</a>.
                                </div>
                                <div class="mb-3">
                                    <label for="billing_method" class="form-label">Billing Method</label>
                                    <select class="form-select" id="billing_method" name="billing_method" required>
                                        <option value="COD">Cash on Delivery (Pay on arrival)</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-lg-5">
                    <div class="order-summary-card p-4">
                        <h4 class="mb-3">Order Summary</h4>
                        <div id="summary-items-list">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item d-flex align-items-center mb-3"
                                    data-id="<?php echo $item['product_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <div class="item-price-details">
                                            <span><?php echo $item['quantity']; ?> x
                                                $<?php echo number_format($item['price_paid'], 2); ?></span>
                                            <?php if ($item['price_paid'] < $item['original_price']): ?>
                                                <span
                                                    class="original-price-summary">$<?php echo number_format($item['original_price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span
                                        class="fw-bold">$<?php echo number_format($item['price_paid'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span id="summary-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-success">
                            <span>Savings</span>
                            <span id="summary-savings">-$<?php echo number_format($total_savings, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Shipping</span>
                            <span id="shipping-cost-display">$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span id="grand-total"
                                data-base-total="<?php echo $final_total; ?>">$<?php echo number_format($final_total, 2); ?></span>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 mt-4">Place Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shippingSelect = document.getElementById('shipping_method');
            const shippingCostDisplay = document.getElementById('shipping-cost-display');
            const grandTotalDisplay = document.getElementById('grand-total');

            shippingSelect.addEventListener('change', function () {
                const shippingCost = parseFloat(this.value) || 0;
                const baseTotal = parseFloat(grandTotalDisplay.dataset.baseTotal);
                shippingCostDisplay.textContent = '$' + shippingCost.toFixed(2);
                grandTotalDisplay.textContent = '$' + (baseTotal + shippingCost).toFixed(2);
            });
        });
    </script>
</body>

</html>