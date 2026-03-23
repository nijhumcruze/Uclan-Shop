<?php
session_start();
require_once 'conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* Add a basic flex container for offers to match product card grid */
    .offers-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin: 20px 0;
    }
    .offer-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 20px;
      width: 300px;
      display: flex;
      flex-direction: column;
    }
    .offer-card h2 {
      margin-top: 0;
      color: #E2231A; /* Primary red */
    }
  </style>
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo">
    <nav>
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="cart.php">Cart</a>
      <?php if(isset($_SESSION['user_id'])): ?>
          <a href="#">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
          <a href="logout.php">Logout</a>
      <?php else: ?>
          <a href="login.php">Log In / Register</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>
    <h1 class="primary-blue-text">Welcome to the Lancashire SU Shop</h1>
    <p>Shop discounted University of Lancashire merchandise!</p>
    
    <div class="video-container" style="margin-bottom: 30px;">
      <video controls autoplay loop muted playsinline style="max-width: 100%; border-radius: 8px;">
        <source src="assets/video.mp4" type="video/mp4">
        Sorry, your browser doesn't support embedded video.
      </video>
    </div>

    <h2 class="primary-blue-text">Current Offers</h2>
    <div class="offers-container">
      <?php
      $sql = "SELECT * FROM tbl_offers";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              echo '<div class="offer-card">';
              echo '<h2>' . htmlspecialchars($row['offer_title']) . '</h2>';
              echo '<p>' . htmlspecialchars($row['offer_desc']) . '</p>';
              echo '</div>';
          }
      } else {
          echo "<p>No offers available at the moment.</p>";
      }
      ?>
    </div>
  </main>
</body>
</html>

