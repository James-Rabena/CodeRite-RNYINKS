<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - RNYINKS</title>
  <!-- Bootstrap 5.0.2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="login.css">
  <link rel="stylesheet" href="headerfooter.css" />
</head>

<body>
  <div class="container">
    <header class="header">
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
        <a href="login.php" class="nav-link">Sign In</a>
        <a href="signup.php" class="signup-btn">Sign Up</a>
        <a href="cart.php" class="cart-link">
          <img
            src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/5e0645d417ccc7b0f84ef323887e2f0a37abc5a3?placeholderIfAbsent=true"
            alt="Shopping cart" class="cart-icon" />
        </a>
      </div>
    </header>

    <!-- Login Page -->
    <div class="login-page d-flex justify-content-center align-items-center vh-100">
      <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
          <h1 class="h4">RNYINKS</h1>
          <h2 class="h5 text-muted">Login</h2>
        </div>

        <!-- Display error message -->
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
          echo "<p style='color: red; text-align: center;'>{$_SESSION['error']}</p>";
          unset($_SESSION['error']);
        }
        ?>

        <!-- Email/Password login -->
        <form action="process_login.php" method="POST">
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password"
              required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>

        <!-- Google Login -->
        <div class="text-center mt-3">
          <button id="googleLoginBtn" class="btn btn-danger w-100">
            <i class="fab fa-google"></i> Sign in with Google
          </button>
        </div>

        <?php
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
          echo '<div class="text-center mt-3">';
          echo '<a href="admindashboard.php" class="btn btn-secondary">Go to Admin Dashboard</a>';
          echo '</div>';
        }
        ?>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-content">
        <div class="footer-column">
          <div class="footer-section">
            <h3 class="footer-heading">About Us</h3>
            <p class="footer-text">
              Crafting unique fragrances that tell stories and create memories.
            </p>
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
              <a href="/shipping" class="footer-link">Shipping Info</a>
              <a href="/returns" class="footer-link">Returns</a>
              <a href="FAQ.php" class="footer-link">FAQ</a>
            </nav>
            <div class="footer-separator"></div>
          </div>
          <div class="footer-section">
            <h3 class="footer-heading">Follow Us</h3>
            <div class="social-links">
              <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"
                class="social-link">
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/c354ca31bc6cdfa9623c3a91eb2fe5873a99b82a?placeholderIfAbsent=true"
                  alt="Facebook" class="social-icon" />
              </a>
              <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"
                class="social-link">
                <img
                  src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/1acf717044a47881336e847420827ecef77ce4a1?placeholderIfAbsent=true"
                  alt="Instagram" class="social-icon" />
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="copyright">
        © <span id="current-year"></span> Fragrance Fusion. All rights reserved.
      </div>
    </footer>
  </div>

  <!-- Firebase v9 modular SDK -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-app.js";
    import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-auth.js";

    const firebaseConfig = {
      apiKey: "AIzaSyDSATkg3AQiM5GNq1a5zDByCRWdqQrUVZk",
      authDomain: "rnyinks-c600f.firebaseapp.com",
      projectId: "rnyinks-c600f",
      storageBucket: "rnyinks-c600f.firebasestorage.app",
      messagingSenderId: "532037944294",
      appId: "1:532037944294:web:6a15fb2b533d31a4c50c27",
      measurementId: "G-355ZHNRZ84"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();

    document.getElementById('googleLoginBtn').addEventListener('click', async () => {
      try {
        const result = await signInWithPopup(auth, provider);
        const token = await result.user.getIdToken();

        // Send token to backend
        const response = await fetch('firebase_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ token })
        });
        const data = await response.json();

        if (data.success) {
          window.location.href = 'index.php';
        } else {
          alert(data.error || "Login failed.");
        }
      } catch (error) {
        alert("Google login failed: " + error.message);
      }
    });
  </script>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="aboutus.js"></script>
</body>

</html>