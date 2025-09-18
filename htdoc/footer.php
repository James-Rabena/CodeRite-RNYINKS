<style>
  /* Footer styles */
  .footer {
    background-color: #ffffff;
    width: 100%;
    padding: 60px 40px;
    margin: 0;
    margin-top: 10px;
  }

  .footer-content {
    display: flex;
    gap: 20px;
    max-width: 1280px;
    margin: 0 auto;
  }

  .footer-column {
    width: 100%;
    display: flex;
    gap: 20px;
  }

  .footer-section {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .footer-heading {
    color: #111111;
    font-size: 1.25rem;
    font-weight: bold;
  }

  .footer-text {
    color: #444444;
    font-size: 1rem;
    margin-top: 27px;
  }

  .footer-nav {
    display: flex;
    flex-direction: column;
  }

  .footer-link {
    color: #444444;
    margin-top: 25px;
    transition: color 0.3s;
  }

  .footer-link:hover {
    color: black;
  }

  .social-links {
    display: flex;
    gap: 24px;
    margin-top: 25px;
  }

  .social-icon {
    width: 24px;
    height: 24px;
  }

  .bar-before-footer {
    margin-top: 40px;
    background-color: #f3f3f3;
    padding: 20px 0;
    text-align: center;
    font-size: 1rem;
    color: #444444;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
  }

  .bar-before-footer p {
    margin: 0;
  }

  #wishlistModal .modal-body {
    max-height: 400px;
    /* Adjust this value as needed */
    overflow-y: auto;
  }
</style>

<?php
if (!isset($_SESSION['user_id'])):
  ?>
  <div class="bar-before-footer">
    <p>Join our community and stay updated!</p>
  </div>
<?php endif; ?>

<footer class="footer">
  <div class="footer-content">
    <div class="footer-column">
      <div class="footer-section">
        <h3 class="footer-heading">About Us</h3>
        <p class="footer-text">Finding unique Literary tools that will write your stories.</p>
      </div>
      <div class="footer-section">
        <h3 class="footer-heading">Quick Links</h3>
        <nav class="footer-nav">
          <a href="collections.php" class="footer-link">Collections</a>
          <a href="ContactForm.php" class="footer-link">Contact Us</a>
        </nav>
      </div>
    </div>
    <div class="footer-column">
      <div class="footer-section">
        <h3 class="footer-heading">Customer Care</h3>
        <nav class="footer-nav">
          <a href="#" class="footer-link">Returns</a>
          <a href="FAQ.php" class="footer-link">FAQ</a>
        </nav>
      </div>
      <div class="footer-section">
        <h3 class="footer-heading">Follow Us</h3>
        <div class="social-links">
          <a href="https://facebook.com" target="_blank"><img
              src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/c354ca31bc6cdfa9623c3a91eb2fe5873a99b82a"
              alt="Facebook" class="social-icon"></a>
          <a href="https://instagram.com" target="_blank"><img src="assets/instagram.png" alt="Instagram"
              class="social-icon"></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Wishlist Modal -->
<div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="wishlistModalLabel">My Wishlist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        if (isset($wishlist_items_for_modal) && !empty($wishlist_items_for_modal)):
          ?>
          <div class="list-group">
            <?php foreach ($wishlist_items_for_modal as $item): ?>
              <!-- FIX: The image and span are now correctly inside the <a> tag -->
              <a href="#" class="list-group-item list-group-item-action d-flex align-items-center wishlist-item-link"
                data-product-id="<?php echo $item['id']; ?>" data-bs-dismiss="modal">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                  alt="<?php echo htmlspecialchars($item['name']); ?>"
                  style="width: 40px; height: 40px; object-fit: cover; margin-right: 15px;">
                <span><?php echo htmlspecialchars($item['name']); ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-center text-muted">Your wishlist is empty.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer justify-content-between">
        <a href="collections.php" class="btn btn-primary">Add More Items</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Combined and Corrected JavaScript Block -->
<script>
  // This function can be called from other pages (like collections.php)
  function updateWishlistModal() {
    const modalBody = document.querySelector('#wishlistModal .modal-body');
    if (!modalBody) return;

    fetch('get_wishlist_items.php')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.items.length > 0) {
          let newContent = `
                        <div class="list-group">`;

          data.items.forEach(item => {
            newContent += `
                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center wishlist-item-link"
                               data-product-id="${item.id}" data-bs-dismiss="modal">
                                <img src="${item.image_url}" alt="${item.name}" style="width: 40px; height: 40px; object-fit: cover; margin-right: 15px;">
                                <span>${item.name}</span>
                            </a>`;
          });

          newContent += `</div>`;
          modalBody.innerHTML = newContent;
        } else {
          modalBody.innerHTML = '<p class="text-center text-muted">Your wishlist is empty.</p>';
        }
      })
      .catch(error => {
        modalBody.innerHTML = '<p class="text-center text-danger">Could not load wishlist.</p>';
      });
  }

  // This block runs once the page is loaded
  document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', function (event) {
      const link = event.target.closest('.wishlist-item-link');
      if (link) {
        event.preventDefault(); // Always prevent the default link behavior
        const productId = link.getAttribute('data-product-id');
        const targetModalElement = document.getElementById(`modal-${productId}`);
        if (targetModalElement) {
          // If the modal exists on THIS page (i.e., we are on collections.php), show it.
          const productModal = new bootstrap.Modal(targetModalElement);
          productModal.show();
        } else {
          // If the modal does NOT exist, redirect to collections.php with a parameter.
          window.location.href = `collections.php?view_product=${productId}`;
        }
      }
      if (event.target.matches('[data-notification-id]')) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.target;
        const notificationId = button.getAttribute('data-notification-id');
        // This correctly finds the parent <li> element that contains the entire notification
        const notificationItem = button.closest('li');

        const formData = new FormData();
        formData.append('notification_id', notificationId);

        fetch('delete_notification.php', { method: 'POST', body: formData })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Instantly remove the notification from view with a fade-out effect
              notificationItem.style.transition = 'opacity 0.3s ease';
              notificationItem.style.opacity = '0';
              setTimeout(() => notificationItem.remove(), 300);

              // Re-poll to update the badge count immediately
              pollForNotifications();
            } else {
              alert('Could not remove notification.');
            }
          });
      }
      // Your existing wishlist-item-link listener should be an 'else if' or separate
      else if (event.target.closest('.wishlist-item-link')) {
        // ... your existing wishlist link logic ...
      }
    });
    const notificationBell = document.getElementById('notificationDropdown');
    if (notificationBell) {
      notificationBell.addEventListener('click', function () {
        const badge = this.querySelector('.badge');

        // If there's a badge, it means there are unread notifications
        if (badge) {
          // Call the backend script to mark them as read
          fetch('mark_notifications_read.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // On success, hide the badge immediately
                badge.style.display = 'none';
              }
            });
        }
      });
    }
  });
  // This function will be called periodically to check for new notifications
  // This is the correct function to dynamically update the notification dropdown.
  // It ensures the HTML structure matches the PHP version.
  function updateNotificationDropdown() {
    fetch('get_notification_status.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const notificationContainer = document.getElementById('notification-list-container');
          const badge = document.querySelector('#notificationDropdown .badge');

          // --- 1. Update Badge Count ---
          if (badge) {
            if (data.count > 0) {
              badge.textContent = data.count;
              badge.style.display = ''; // Make sure it's visible
            } else {
              badge.style.display = 'none'; // Hide if count is 0
            }
          } else if (data.count > 0) {
            // If badge doesn't exist but should, you might need to recreate it.
            // For now, we'll assume it's handled on page load.
          }

          // --- 2. Clear the old notification list ---
          notificationContainer.innerHTML = '';

          // --- 3. Rebuild the list with the correct HTML structure ---
          if (data.items.length > 0) {
            data.items.forEach(notif => {
              // Create the LI element with the correct class and data-attribute
              const li = document.createElement('li');
              li.className = 'notification-item-li';
              li.setAttribute('data-notification-id', notif.id);

              // Use innerHTML for the complex content
              li.innerHTML = `
                            <div class="notification-item">
                                <a href="${notif.link}" class="notification-link">
                                    <img src="${notif.image_url || 'assets/product-placeholder.png'}" alt="Product" class="notification-item-image">
                                    <div>
                                        <span class="notification-text" style="${notif.is_read ? '' : 'font-weight: bold;'}">
                                            ${notif.message}
                                        </span>
                                    </div>
                                </a>
                                <button type="button" class="btn-close notification-dismiss-btn" aria-label="Close"></button>
                            </div>
                        `;
              notificationContainer.appendChild(li);
            });
          } else {
            // Display the "No new notifications" message
            notificationContainer.innerHTML = '<li><span class="dropdown-item text-muted">No new notifications.</span></li>';
          }
        }
      })
      .catch(error => {
        console.error("Error fetching live notifications:", error);
      });
  }
  // Check for new notifications every 30 seconds
  setInterval(updateNotificationDropdown, 30000);
</script>