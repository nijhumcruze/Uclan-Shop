<?php
session_start();
require_once 'conn.php';

// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT * FROM tbl_products";
if ($filter == 'in') {
    $sql .= " WHERE product_stock = 'good-stock' OR product_stock = 'low-stock'";
} elseif ($filter == 'out') {
    $sql .= " WHERE product_stock = 'out-of-stock'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo" />
    <nav>
      <a href="index.php">Home</a>
      <a href="products.php" class="active">Products</a>
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
    <h1 class="primary-blue-text home-title">Shop Our Merchandise</h1>

    <div style="margin-bottom: 20px;">
      <form action="products.php" method="GET" id="filterForm">
        <label for="filterSelect">Show:</label>
        <select id="filterSelect" name="filter" onchange="document.getElementById('filterForm').submit();">
          <option value="all" <?php if($filter == 'all') echo 'selected'; ?>>All</option>
          <option value="in" <?php if($filter == 'in') echo 'selected'; ?>>In Stock</option>
          <option value="out" <?php if($filter == 'out') echo 'selected'; ?>>Out of Stock</option>
        </select>
      </form>
    </div>

    <div class="cards-container" id="cards-container">
      <?php
      if ($result && $result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $is_out_of_stock = ($row['product_stock'] === 'out-of-stock');
              $stock_text = str_replace('-', ' ', ucwords($row['product_stock']));
              
              // Calculate avg rating if possible (optional but good for UI)
              $pid = $row['product_id'];
              $rating_sql = "SELECT AVG(review_rating) as avg_rating, COUNT(*) as rev_count FROM tbl_reviews WHERE product_id = $pid";
              $rating_res = $conn->query($rating_sql);
              $rev_data = $rating_res->fetch_assoc();
              $avg_rating = round($rev_data['avg_rating'] ?? 0);
              $rev_count = $rev_data['rev_count'];

              echo '<div class="card">';
              echo '  <div class="badge">' . ($row['product_stock'] == 'low-stock' ? 'HURRY' : '') . '</div>';
              echo '  <div class="tilt">';
              echo '    <div class="img">';
              echo '      <a href="item.php?id=' . $row['product_id'] . '">';
              echo '        <img src="' . htmlspecialchars($row['product_src']) . '" alt="' . htmlspecialchars($row['product_title']) . '">';
              echo '      </a>';
              echo '    </div>';
              echo '  </div>';
              echo '  <div class="info">';
              echo '    <h2 class="title">' . htmlspecialchars($row['product_title']) . '</h2>';
              echo '    <p class="desc">' . htmlspecialchars($row['product_desc']) . '</p>';
              echo '    <div class="bottom">';
              echo '      <div class="price">';
              echo '        <span class="new">£' . htmlspecialchars($row['product_price']) . '</span>';
              echo '      </div>';
              
              // Add to cart form
              echo '      <form action="cart.php" method="POST" style="margin:0;">';
              echo '        <input type="hidden" name="action" value="add">';
              echo '        <input type="hidden" name="product_id" value="' . $row['product_id'] . '">';
              
              if (!isset($_SESSION['user_id'])) {
                  // Redirect guests to login by making the form action login.php with a message
                  echo '        <button type="submit" formaction="login.php?msg=login_required" class="btn" ' . ($is_out_of_stock ? 'disabled style="opacity:0.5;"' : '') . '>';
              } else {
                  echo '        <button type="submit" class="btn" ' . ($is_out_of_stock ? 'disabled style="opacity:0.5;"' : '') . '>';
              }
              
              echo '          <span>Add to Cart</span>';
              echo '          <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
              echo '            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 01-8 0"></path>';
              echo '          </svg>';
              echo '        </button>';
              echo '      </form>';
              
              echo '    </div>';
              echo '    <div class="meta">';
              echo '      <div class="rating">';
              for ($i=0; $i<5; $i++) {
                  $color = ($i < $avg_rating) ? '#FFD700' : '#ccc';
                  echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="'.$color.'" stroke="'.$color.'" stroke-width="0.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
              }
              echo '        <span class="rcount">' . $rev_count . ' Reviews</span>';
              echo '      </div>';
              echo '      <div class="stock ' . ($is_out_of_stock ? 'out' : '') . '">' . htmlspecialchars($stock_text) . '</div>';
              echo '    </div>';
              echo '  </div>';
              // form end was inside
              echo '</div>';
          }
      } else {
          echo "<p>No products found.</p>";
      }
      ?>
    </div>
  </main>
</body>
</html>

