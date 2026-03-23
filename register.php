<?php
session_start();
require_once 'conn.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);

    // Server-side validation
    if (empty($name) || empty($email) || empty($password) || empty($address)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM tbl_users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $stmt->close();
            // Create user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO tbl_users (user_name, user_email, user_pass, user_address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $address);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                $error = "Error saving user. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .auth-form { max-width: 400px; margin: 40px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .auth-form h2 { margin-top: 0; color: #002366; }
    .auth-form input, .auth-form textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .auth-form button { width: 100%; padding: 10px; background: #E2231A; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    .auth-form button:hover { background: #c11b13; }
    .error { color: #E2231A; margin-bottom: 15px; font-weight: bold; }
    .success { color: #28a745; margin-bottom: 15px; font-weight: bold; }
  </style>
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo">
    <nav>
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="cart.php">Cart</a>
      <a href="login.php">Log In / Register</a>
    </nav>
  </header>
  <main>
    <div class="auth-form">
      <h2>Register</h2>
      <?php if($error) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
      <?php if($success) echo "<p class='success'>" . $success . "</p>"; ?>
      
      <?php if(!$success): ?>
      <form action="register.php" method="POST">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
        
        <label for="address">Postal Address</label>
        <textarea id="address" name="address" rows="3" required></textarea>
        
        <button type="submit">Create Account</button>
      </form>
      <?php endif; ?>
      <p style="text-align: center; margin-top: 20px;">Already have an account? <a href="login.php">Log in here</a></p>
    </div>
  </main>
</body>
</html>
