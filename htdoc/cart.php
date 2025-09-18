<?php
session_start();
require_once __DIR__ . '/db_connection.php';

$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
$cartItems = [];
$subtotal = 0;
$total_savings = 0;
$final_total = 0;

if ($is_logged_in) {
  $user_id = $_SESSION['user_id'];
  $sql = "SELECT ci.*, p.name AS product_name, p.image_url, p.price AS original_price
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    if ($row['active'] == 1) {
      $subtotal += $row['original_price'] * $row['quantity'];
      $final_total += $row['price'] * $row['quantity'];
    }
  }
  $total_savings = $subtotal - $final_total;
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RNYINKS - Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="cart.css">
  <link rel="stylesheet" href="headerfooter.css">
  <style>
    .quantity-controls {
      display: flex;
      align-items: center;
    }

    .quantity-controls button:last-of-type {
      order: 1;
    }

    .quantity-controls .quantity-input {
      order: 2;
      width: 60px;
      text-align: center;
      border: none;
      border-radius: 0;
    }

    .quantity-controls button:first-of-type {
      order: 3;
      width: 35px;
    }

    .cart-item img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-right: 15px;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>
  <div class="container my-5 cart-container">
    <h2 class="mb-4">Shopping Cart</h2>
    <div class="row">
      <div class="col-md-8">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Items</h5>
            <button class="btn btn-sm btn-outline-danger" id="remove-all-btn">Remove All</button>
          </div>
          <div id="cart-items-list">
            <?php if ($is_logged_in && count($cartItems) > 0): ?>
              <?php foreach ($cartItems as $item): ?>
                <div class="cart-item d-flex align-items-center justify-content-between mb-3"
                  data-id="<?php echo $item['id']; ?>" data-original-price="<?php echo $item['original_price']; ?>">
                  <div class="form-check me-3">
                    <input class="form-check-input item-active-checkbox" type="checkbox" <?php echo $item['active'] ? 'checked' : ''; ?>>
                  </div>
                  <div class="d-flex align-items-center flex-grow-1">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                      alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div>
                      <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                      <p class="mb-1 price" data-price="<?php echo $item['price']; ?>">$
                        <?php echo number_format($item['price'], 2); ?>
                      </p>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <div class="quantity-controls me-4">
                      <button class="btn btn-secondary btn-sm"
                        onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                      <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                      <button class="btn btn-secondary btn-sm"
                        onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                    </div>
                    <button class="btn btn-danger btn-sm" onclick="removeItem(<?php echo $item['id']; ?>)">×</button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div id="empty-cart-message" class="text-center" style="display: none;">
            <p>Your cart is empty.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-3">
          <h5>Order Summary</h5>
          <p>Subtotal: $<span id="subtotal"><?php echo number_format($subtotal, 2); ?></span></p>
          <p class="text-success" <?php echo ($total_savings > 0) ? '' : 'style="display: none;"'; ?>>
            Savings: -$<span id="savings"><?php echo number_format($total_savings, 2); ?></span>
          </p>
          <hr>
          <p><strong>Total: $<span id="total"><?php echo number_format($final_total, 2); ?></span></strong></p>
          <button class="btn btn-dark w-100 mt-3" onclick="proceedToCheckout()">Checkout</button>
        </div>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>

  <script>
    const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;

    document.addEventListener('DOMContentLoaded', function () {
      updateCartState();

      // Checkbox toggle
      document.querySelectorAll('.item-active-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
          const cartId = this.closest('.cart-item').dataset.id;
          const active = this.checked ? 1 : 0;
          if (isLoggedIn) {
            fetch('update_cart_active.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `cart_id=${cartId}&active=${active}`
            }).then(res => res.json())
              .then(data => updateSummary());
          }
        });
      });

      // Remove All
      document.getElementById('remove-all-btn').addEventListener('click', function () {
        document.querySelectorAll('.cart-item').forEach(item => item.remove());
        if (isLoggedIn) {
          fetch('cart_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'clear' })
          });
        }
        updateCartState();
      });
    });

    function updateQuantity(cartId, change) {
      const itemElement = document.querySelector(`.cart-item[data-id='${cartId}']`);
      const input = itemElement.querySelector('.quantity-input');
      let newQuantity = parseInt(input.value) + change;
      if (newQuantity < 1) { removeItem(cartId); return; }
      input.value = newQuantity;

      if (isLoggedIn) {
        fetch('cart_api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'update', product_id: cartId, quantity: newQuantity })
        });
      }

      updateSummary();
    }

    function removeItem(cartId) {
      const itemElement = document.querySelector(`.cart-item[data-id='${cartId}']`);
      if (itemElement) itemElement.remove();
      if (isLoggedIn) {
        fetch('cart_api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'remove', product_id: cartId })
        });
      }
      updateCartState();
    }

    function updateSummary() {
      let subtotal = 0;
      let finalTotal = 0;

      document.querySelectorAll('.cart-item').forEach(item => {
        const checkbox = item.querySelector('.item-active-checkbox');
        if (!checkbox.checked) return;
        const price = parseFloat(item.querySelector('.price').dataset.price);
        const originalPrice = parseFloat(item.dataset.originalPrice);
        const quantity = parseInt(item.querySelector('.quantity-input').value);
        subtotal += originalPrice * quantity;
        finalTotal += price * quantity;
      });

      const savings = subtotal - finalTotal;
      document.getElementById('subtotal').textContent = subtotal.toFixed(2);
      document.getElementById('total').textContent = finalTotal.toFixed(2);
      const savingsEl = document.getElementById('savings');
      savingsEl.textContent = savings.toFixed(2);
      savingsEl.parentElement.style.display = savings > 0 ? 'block' : 'none';
    }

    function updateCartState() {
      const hasItems = document.querySelectorAll('.cart-item').length > 0;
      document.getElementById('empty-cart-message').style.display = hasItems ? 'none' : 'block';
      document.getElementById('remove-all-btn').style.display = hasItems ? 'block' : 'none';
      updateSummary();
    }

    function proceedToCheckout() {
      const subtotal = parseFloat(document.getElementById('subtotal').textContent);
      if (subtotal <= 0) {
        alert("Your cart is empty. Add items before checking out.");
        return;
      }
      window.location.href = "checkout_mail.php";
    }
  </script>
</body>

</html>