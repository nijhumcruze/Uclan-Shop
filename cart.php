<?php
session_start();
require_once 'conn.php';

// Cart Cookie Management
$cookie_name = "lsu_cart";
$cart = isset($_COOKIE[$cookie_name]) ? json_decode($_COOKIE[$cookie_name], true) : [];
if (!is_array($cart)) $cart = [];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'add') {
        $pid = intval($_POST['product_id']);
        if (isset($cart[$pid])) {
            $cart[$pid]++;
        } else {
            $cart[$pid] = 1;
        }
        setcookie($cookie_name, json_encode($cart), time() + (86400 * 30), "/"); // 30 days
        header("Location: cart.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'remove') {
        $pid = intval($_POST['product_id']);
        if (isset($cart[$pid])) {
            unset($cart[$pid]);
        }
        setcookie($cookie_name, json_encode($cart), time() + (86400 * 30), "/");
        header("Location: cart.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'update') {
        $pid = intval($_POST['product_id']);
        $qty = intval($_POST['quantity']);
        if ($qty > 0) {
            $cart[$pid] = $qty;
        } else {
            unset($cart[$pid]);
        }
        setcookie($cookie_name, json_encode($cart), time() + (86400 * 30), "/");
        header("Location: cart.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'empty') {
        $cart = [];
        setcookie($cookie_name, "", time() - 3600, "/");
        header("Location: cart.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'discount') {
        $code = trim($_POST['code']);
        if (strtoupper($code) === 'GRAD25') {
            $_SESSION['discount'] = 25;
        } else {
            $_SESSION['discount'] = 0;
            $_SESSION['discount_error'] = "Invalid discount code.";
        }
        header("Location: cart.php");
        exit();
    }

    elseif ($_POST['action'] === 'checkout') {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php?msg=login_required");
            exit();
        }
        
        if (!empty($cart)) {
            $user_id = $_SESSION['user_id'];
            
            // Build a comma separated string of product_ids (e.g., if item 1 has qty 2, "1,1")
            $product_ids_arr = [];
            foreach ($cart as $pid => $qty) {
                for($i = 0; $i < $qty; $i++) {
                    $product_ids_arr[] = $pid;
                }
            }
            $product_ids_str = implode(",", $product_ids_arr);
            
            $stmt = $conn->prepare("INSERT INTO tbl_orders (user_id, product_ids) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $product_ids_str);
            if ($stmt->execute()) {
                // Clear cart on successful order
                setcookie($cookie_name, "", time() - 3600, "/");
                $_SESSION['checkout_success'] = "Thank you for your order! Your purchase has been recorded.";
                unset($_SESSION['discount']);
                header("Location: cart.php");
                exit();
            }
            $stmt->close();
        }
    }
}

// Fetch Cart Products
$cart_items = [];
$total_price = 0;
$total_qty = 0;

if (!empty($cart)) {
    // Sanitize keys for IN clause
    $keys = array_map('intval', array_keys($cart));
    $in_clause = implode(',', $keys);
    
    $result = $conn->query("SELECT * FROM tbl_products WHERE product_id IN ($in_clause)");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pid = $row['product_id'];
            $qty = $cart[$pid];
            $row['cart_qty'] = $qty;
            $row['subtotal'] = $row['product_price'] * $qty;
            $total_price += $row['subtotal'];
            $total_qty += $qty;
            $cart_items[] = $row;
        }
    }
}

$discount_percent = $_SESSION['discount'] ?? 0;
$discount_amount = ($total_price * $discount_percent) / 100;
$final_price = $total_price - $discount_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .cart-card { display: flex; background: white; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; max-width: 600px; }
    .cart-card img { width: 150px; height: 150px; object-fit: cover; }
    .cart-card .info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .cart-card form { display: inline-block; margin: 0; }
    .cart-summary { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 400px; margin-top: 20px; }
    .msg-success { color: white; background-color: #28a745; padding: 15px; border-radius: 4px; margin-bottom: 20px; max-width: 600px; font-weight: bold; }
    .msg-error { color: #E2231A; font-weight: bold; margin-bottom: 10px; }
    .discount-form { margin: 20px 0; }
    .discount-form input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .discount-form button { padding: 8px 15px; background: #002366; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .cart-actions form { display: inline-block; margin-right: 10px; }
  </style>
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo">
    <nav>
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="cart.php" class="active">Cart (<?php echo $total_qty; ?>)</a>
      <?php if(isset($_SESSION['user_id'])): ?>
          <a href="#">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
          <a href="logout.php">Logout</a>
      <?php else: ?>
          <a href="login.php">Log In / Register</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <div class="container">
      <h1 class="primary-blue-text">Your Shopping Cart</h1>

      <?php
      if (isset($_SESSION['checkout_success'])) {
          echo '<div class="msg-success">' . $_SESSION['checkout_success'] . '</div>';
          unset($_SESSION['checkout_success']);
      }
      ?>

      <?php if (empty($cart_items)): ?>
          <p>Your cart is currently empty. <a href="products.php">Continue shopping</a></p>
      <?php else: ?>
          
          <div class="cart-actions" style="margin-bottom: 20px;">
              <form action="cart.php" method="POST">
                  <input type="hidden" name="action" value="empty">
                  <button type="submit" class="button" style="background:#E2231A; color:white;" onclick="return confirm('Empty cart?');">Empty Cart</button>
              </form>
          </div>

          <div class="cart-items">
              <?php foreach ($cart_items as $item): ?>
                  <div class="cart-card">
                      <img src="<?php echo htmlspecialchars($item['product_src']); ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                      <div class="info">
                          <h3><a href="item.php?id=<?php echo $item['product_id']; ?>" style="text-decoration:none; color:inherit;"><?php echo htmlspecialchars($item['product_title']); ?></a></h3>
                          <div style="color: #666; margin-bottom: 10px;">Price: £<?php echo htmlspecialchars($item['product_price']); ?> | Subtotal: £<?php echo number_format($item['subtotal'], 2); ?></div>
                          
                          <div style="display: flex; gap: 10px; align-items: center;">
                              <form action="cart.php" method="POST">
                                  <input type="hidden" name="action" value="update">
                                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                  <input type="number" name="quantity" value="<?php echo $item['cart_qty']; ?>" min="1" style="width:60px; padding:5px; border:1px solid #ccc; border-radius:4px;">
                                  <button type="submit" style="padding:6px; background:#002366; color:white; border:none; border-radius:4px; cursor:pointer;">Update</button>
                              </form>

                              <form action="cart.php" method="POST">
                                  <input type="hidden" name="action" value="remove">
                                  <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                  <button type="submit" style="padding:6px; background:#E2231A; color:white; border:none; border-radius:4px; cursor:pointer;">Remove</button>
                              </form>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>

          <div class="discount-form">
              <?php 
              if(isset($_SESSION['discount_error'])) {
                  echo '<div class="msg-error">'.$_SESSION['discount_error'].'</div>';
                  unset($_SESSION['discount_error']);
              }
              ?>
              <form action="cart.php" method="POST">
                  <input type="hidden" name="action" value="discount">
                  <input type="text" name="code" placeholder="Promo Code (e.g. GRAD25)" value="<?php echo ($discount_percent > 0) ? 'GRAD25' : ''; ?>">
                  <button type="submit">Apply Code</button>
              </form>
          </div>

          <div class="cart-summary">
              <h3>Order Summary</h3>
              <p><strong>Total:</strong> £<?php echo number_format($total_price, 2); ?></p>
              <?php if ($discount_percent > 0): ?>
                  <p><strong>Discount (<?php echo $discount_percent; ?>%):</strong> -£<?php echo number_format($discount_amount, 2); ?></p>
              <?php endif; ?>
              <p style="font-size: 1.2rem; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
                  <strong>Amount to Pay:</strong> £<?php echo number_format($final_price, 2); ?>
              </p>
              
              <form action="cart.php" method="POST" style="margin-top: 20px;">
                  <input type="hidden" name="action" value="checkout">
                  <button type="submit" class="button buy-now" style="width: 100%; font-size: 1.1rem; padding: 15px;">Checkout Now</button>
              </form>
          </div>

      <?php endif; ?>
    </div>
  </main>
</body>
</html>

