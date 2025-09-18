<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db_connection.php';

// Initialize all variables
$cartCount = 0;
$notificationCount = 0;
$notifications_for_display = [];
$wishlist_items_for_modal = [];

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
  $user_id = $_SESSION['user_id'];


  // --- Fetch Unread Notification Count for Badge (Corrected) ---
  $stmt_count = $conn->prepare("SELECT COUNT(id) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
  $stmt_count->bind_param("i", $user_id);
  $stmt_count->execute();
  $notificationCount = $stmt_count->get_result()->fetch_assoc()['unread_count'] ?? 0;
  $stmt_count->close();

  // --- Fetch Unread Notification List for Display (Corrected) ---
  $stmt_fetch_notifs = $conn->prepare(
    "SELECT n.id, n.message, n.link, n.is_read, n.created_at, p.image_url
     FROM notifications n
     JOIN products p ON n.product_id = p.id
     WHERE n.user_id = ? AND n.is_read = 0
     ORDER BY n.created_at DESC LIMIT 5"
  );
  $stmt_fetch_notifs->bind_param("i", $user_id);
  $stmt_fetch_notifs->execute();
  $notifications_for_display = $stmt_fetch_notifs->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt_fetch_notifs->close();

  // --- Fetch other user data ---
  $stmt_cart = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
  $stmt_cart->bind_param("i", $user_id);
  $stmt_cart->execute();
  $cartCount = $stmt_cart->get_result()->fetch_assoc()['cart_count'] ?? 0;
  $stmt_cart->close();

  $stmt_wishlist = $conn->prepare("SELECT p.id, p.name, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
  $stmt_wishlist->bind_param("i", $user_id);
  $stmt_wishlist->execute();
  $wishlist_items_for_modal = $stmt_wishlist->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt_wishlist->close();
}

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header("Location: index.php");
  exit();
}
?>
<header class="header">
  <style>
    /* Base styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    }

    body {
      background-color: white;
      color: #111111;
      line-height: 1.5;
      padding-top: 80px;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      max-width: 1280px;
      margin: 0 auto;
      overflow: hidden;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* Header styles */
    .header {
      background-color: white;
      display: flex;
      width: 100%;
      justify-content: space-between;
      padding: 17px 39px;
      align-items: center;
      flex-wrap: wrap;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .header-content {
      width: 100%;
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 31px;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: #111111;
      text-decoration: none;
    }

    .logo:hover {
      color: #111111;
      text-decoration: none;
    }

    .main-nav {
      display: flex;
      align-items: center;
      gap: 31px;
      font-size: 1rem;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 23px;
      text-align: center;
    }

    .nav-link {
      color: #111111;
      text-decoration: none;
      transition: transform 0.3s ease, color 0.3s ease;
    }

    .nav-link:hover {
      transform: scale(1.1);
      color: #333333;
    }

    .signup-btn {
      border-radius: 4px;
      background-color: #111111;
      color: white;
      padding: 8px 16px;
      transition: background-color 0.3s ease;
      text-decoration: none;
    }

    .signup-btn:hover {
      background-color: #333333;
      color: white;
      text-decoration: none;
    }

    .cart-icon {
      width: 24px;
      height: 24px;
      object-fit: contain;
    }

    .cart-link {
      position: relative;
      display: inline-block;
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 0.25em 0.6em;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .logout-link {
      color: red;
      font-weight: bold;
      transition: color 0.3s ease;
    }

    .logout-link:hover {
      color: darkred;
    }

    .notification-item-image {
      width: 50px;
      height: 50px;
      object-fit: contain;
      margin-right: 15px;
      border-radius: 4px;
    }

    .notification-item-li {
      list-style-type: none;
    }

    .notification-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 0.5rem 1rem;
    }

    .notification-link {
      display: flex;
      align-items: center;
      flex-grow: 1;
      text-decoration: none;
      color: inherit;
      overflow: hidden;
    }

    .notification-text {
      white-space: normal;
      word-wrap: break-word;
      overflow: visible;
      text-overflow: clip;
      display: block;
      font-size: 0.875rem;
      line-height: 1.4;
    }

    .notification-dismiss-btn {
      flex-shrink: 0;
      margin-left: 10px;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <div class="header-content">
    <div class="header-left">
      <a href="index.php" class="logo">RNYINKS</a>
      <nav class="main-nav">
        <a href="collections.php" class="nav-link">Collections</a>
        <a href="AboutUs.php" class="nav-link">About</a>
        <a href="ContactForm.php" class="nav-link">Contact</a>
        <a href="FAQ.php" class="nav-link">FAQ</a>
      </nav>
    </div>
    <div class="header-right">

      <!-- Notification Bell Dropdown -->
      <div class="dropdown">
        <a href="#" class="nav-link position-relative" id="notificationDropdown" data-bs-toggle="dropdown"
          aria-expanded="false">
          <i class="fas fa-bell"></i>
          <?php if ($notificationCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size: 0.6em;"><?php echo $notificationCount; ?></span>
          <?php endif; ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 350px;">
          <li class="px-3 py-2"><strong>Notifications</strong></li>
          <li>
            <hr class="dropdown-divider">
          </li>

          <div id="notification-list-container">
            <?php if (empty($notifications_for_display)): ?>
              <li><span class="dropdown-item text-muted">No new notifications.</span></li>
            <?php else: ?>
              <?php foreach ($notifications_for_display as $notif): ?>
                <li class="notification-item-li" data-notification-id="<?php echo $notif['id']; ?>">
                  <div class="notification-item">
                    <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="notification-link">
                      <img src="<?php echo htmlspecialchars($notif['image_url'] ?? 'assets/product-placeholder.png'); ?>"
                        alt="Product" class="notification-item-image">
                      <div>
                        <span class="notification-text"
                          style="<?php echo $notif['is_read'] ? '' : 'font-weight: bold;'; ?>">
                          <?php echo htmlspecialchars($notif['message']); ?>
                        </span>
                      </div>
                    </a>
                    <?php if (empty($notif['is_persistent'])): // Only show dismiss button if notification is NOT persistent ?>
                      <button type="button" class="btn-close notification-dismiss-btn" aria-label="Close"></button>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item text-center small" href="notifications.php">See all notifications</a></li>
        </ul>
      </div> <!-- End of Notification Dropdown Div -->

      <!-- User Profile Dropdown / Sign In Buttons -->
      <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center nav-link dropdown-toggle" id="userDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            <?php
            $profilePic = $_SESSION['profile_picture'] ?? '';
            $userName = $_SESSION['user_name'] ?? 'User';
            $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=dddddd&color=111111';
            if ($profilePic && file_exists($profilePic)) {
              $imgSrc = htmlspecialchars($profilePic) . '?t=' . time();
            }
            ?>
            <img src="<?php echo $imgSrc; ?>" alt="Profile"
              style="width:28px; height:28px; border-radius:50%; object-fit:cover; margin-right: 8px;">
            <span style="font-weight: bold;"><?php echo htmlspecialchars($userName); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item d-flex justify-content-between align-items-center" href="cart.php">
                My Cart
                <span id="header-cart-badge" class="badge bg-danger rounded-pill" <?php echo ($cartCount > 0) ? '' : 'style="display: none;"'; ?>>
                  <?php echo $cartCount; ?>
                </span>
              </a>
            </li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#wishlistModal">My Wishlist</a>
            </li>
            <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li> <!-- ✅ Added -->
            <li><a class="dropdown-item" href="reviews.php">My Reviews</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item logout-link" href="?logout=true">Logout</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="login.php" class="nav-link">Sign In</a>
        <a href="signup.php" class="signup-btn">Sign Up</a>
      <?php endif; ?>
    </div> <!-- End of header-right Div -->
  </div> <!-- End of header-content Div -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const notificationContainer = document.getElementById('notification-list-container');
      const checkForSales = () => {
        fetch('check_sales.php')
          .then(response => response.json())
          .then(data => {
            if (data.success && data.new_notifications > 0) {
              // New sales were found! The next time get_notifications.php runs,
              // it will see them and update the bell icon.
              console.log(data.new_notifications + ' new sale notification(s) created.');
            }
          })
          .catch(error => console.error('Error checking for sales:', error));
      };
      checkForSales();
      setInterval(checkForSales, 18000);

      if (notificationContainer) {
        notificationContainer.addEventListener('click', function (event) {
          if (event.target.classList.contains('notification-dismiss-btn')) {
            event.preventDefault();
            event.stopPropagation();

            const button = event.target;
            const notificationLi = button.closest('.notification-item-li');

            if (!notificationLi) {
              console.error("CRITICAL: Could not find parent '.notification-item-li'. Please ensure the notification HTML in header.php is correct.");
              alert("A client-side error occurred. The HTML structure for notifications appears to be incorrect.");
              return;
            }

            const notificationId = notificationLi.dataset.notificationId;
            if (!notificationId) {
              console.error('Notification ID not found in dataset.');
              return;
            }

            button.disabled = true;
            const formData = new FormData();
            formData.append('notification_id', notificationId);

            fetch('mark_notification_read.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  notificationLi.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                  notificationLi.style.opacity = '0';
                  notificationLi.style.transform = 'translateX(20px)';
                  setTimeout(() => {
                    notificationLi.remove();
                    const badge = document.querySelector('#notificationDropdown .badge');
                    if (badge) {
                      let count = parseInt(badge.textContent) - 1;
                      if (count > 0) {
                        badge.textContent = count;
                      } else {
                        badge.remove();
                      }
                    }
                  }, 300);
                } else {
                  alert(data.message || 'Failed to dismiss notification.');
                  button.disabled = false;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                button.disabled = false;
              });
          }
        });
      }
    });
  </script>
</header>