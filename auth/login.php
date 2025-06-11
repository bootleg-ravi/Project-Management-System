<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Stratify</title>
  <link rel="stylesheet" href="assets/css/stratify-modern.css" />
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: Arial, sans-serif;
    }

    body {
      display: flex;
      flex-direction: column;
      background: linear-gradient(to bottom right, #123C58 50%, #ffffff 50%);
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 40px;
    }

    .logo {
      font-size: 20px;
      color: #000;
      font-weight: bold;
    }

    .top-bar a {
      border: 1px solid #000;
      padding: 5px 15px;
      text-decoration: none;
      color: #000;
      font-weight: bold;
    }

    main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .auth-container {
      background: white;
      max-width: 400px;
      width: 100%;
      padding: 40px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 6px;
      text-align: center;
    }

    .auth-container h2 {
      margin-bottom: 30px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
    }

    .remember-forgot {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
    }

    .auth-container button {
      width: 100%;
      padding: 12px;
      background: #000;
      color: white;
      border: none;
      margin-top: 20px;
      font-weight: bold;
    }

    .divider {
      margin: 20px 0;
      position: relative;
      text-align: center;
    }

    .divider::before,
    .divider::after {
      content: '';
      position: absolute;
      top: 50%;
      width: 45%;
      height: 1px;
      background: #ccc;
    }

    .divider::before {
      left: 0;
    }

    .divider::after {
      right: 0;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      padding: 20px;
      color: #666;
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <div class="logo">● Stratify</div>
    <a href="register.php">SIGN UP</a>
  </div>

  <main>
    <div class="auth-container">
      <h2>Log In to Stratify</h2>
      <form action="../backend/login_process.php" method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <div class="remember-forgot">
          <label><input type="checkbox" name="remember"> Remember me</label>
          <a href="#">Forgot Password?</a>
        </div>

        <button type="submit">PROCEED</button>

        <div class="divider">OR</div>

        <div class="social-icons">
          <img src="../assets/icons/google.png" alt="Google" width="24">
          <img src="../assets/icons/apple.png" alt="Apple" width="24">
          <img src="../assets/icons/facebook.png" alt="Facebook" width="24">
        </div>
      </form>
    </div>
  </main>

  <div class="footer">
    &copy; 2025 All Rights Reserved. Stratify.
  </div>

</body>
</html>
