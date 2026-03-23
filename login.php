<?php
session_start();
require_once 'conn.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, user_name, user_pass FROM tbl_users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['user_pass'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['user_name'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
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
  <title>Login | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .auth-form { max-width: 400px; margin: 40px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .auth-form h2 { margin-top: 0; color: #002366; }
    .auth-form input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .auth-form button { width: 100%; padding: 10px; background: #E2231A; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    .auth-form button:hover { background: #c11b13; }
    .error { color: #E2231A; margin-bottom: 15px; font-weight: bold; }
  </style>
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo">
    <nav>
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="cart.php">Cart</a>
      <a href="login.php" class="active">Log In / Register</a>
    </nav>
  </header>
  <main>
    <div class="auth-form">
      <h2>Log In</h2>
      <?php if($error) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
      <form action="login.php" method="POST">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Log In</button>
      </form>
      <p style="text-align: center; margin-top: 20px;">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
  </main>
</body>
</html>
