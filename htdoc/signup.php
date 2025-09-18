<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup - RNYINKS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="signup.css">
  <link rel="stylesheet" href="headerfooter.css">
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
            src="https://cdn.builder.io/api/v1/image/assets/ce8c66c9a0c34d0f9a6ae9ddc010af6e/5e0645d417ccc7b0f84ef323887e2f0a37abc5a3"
            alt="Shopping cart" class="cart-icon" />
        </a>
      </div>
    </header>

    <div class="signup-page d-flex justify-content-center align-items-center vh-100">
      <div class="card p-4 shadow" style="width:100%;max-width:500px;">
        <div class="text-center mb-4">
          <h1 class="h4">RNYINKS</h1>
          <p class="text-muted">Create your account to explore the best luxurious pens and papers.</p>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
          echo "<div class='alert alert-danger text-center'>{$_SESSION['error']}</div>";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success text-center'>{$_SESSION['success']}</div>";
          unset($_SESSION['success']);
        }
        ?>

        <!-- Email/password signup -->
        <form id="signupForm">
          <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPassword" required>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Create Account</button>
          </div>
        </form>

        <!-- Google sign up -->
        <div class="text-center mt-3">
          <button id="googleSignupBtn" class="btn btn-danger w-100">
            <i class="fab fa-google"></i> Sign up with Google
          </button>
        </div>

        <div class="text-center mt-3">
          <p class="small">Already have an account? <a href="login.php" class="text-decoration-none">Sign in</a></p>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-content"></div>
      <div class="copyright">
        © <span id="current-year"></span> Fragrance Fusion. All rights reserved.
      </div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-app.js";
    import {
      getAuth,
      createUserWithEmailAndPassword,
      sendEmailVerification,
      signInWithPopup,
      GoogleAuthProvider
    } from "https://www.gstatic.com/firebasejs/9.22.2/firebase-auth.js";

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
    auth.useDeviceLanguage();
    const provider = new GoogleAuthProvider();

    // Email/password signup
    document.getElementById('signupForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      if (password !== confirmPassword) {
        alert("Passwords do not match");
        return;
      }
      try {
        const userCred = await createUserWithEmailAndPassword(auth, email, password);
        await sendEmailVerification(userCred.user);
        alert('Verification email sent to ' + userCred.user.email);
        await fetch('firebase_signup.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            firstName: firstName,
            lastName: lastName,
            email: email,
            password: password,
            confirmPassword: confirmPassword
          })
        });
        window.location.href = 'login.php';
      } catch (err) {
        alert('Error: ' + err.message);
      }
    });

    // Google signup (no verification email—Google is already verified)
    document.getElementById('googleSignupBtn').addEventListener('click', async () => {
      try {
        const result = await signInWithPopup(auth, provider);
        const user = result.user;
        alert('Signed up with Google as ' + user.email);
        // Save to your DB
        await fetch('firebase_signup.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            firstName: user.displayName?.split(' ')[0] || '',
            lastName: user.displayName?.split(' ')[1] || '',
            email: user.email,
            password: '',
            confirmPassword: ''
          })
        });
        window.location.href = 'login.php';
      } catch (err) {
        alert('Google signup failed: ' + err.message);
      }
    });
  </script>
</body>

</html>