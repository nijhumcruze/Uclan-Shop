<?php
session_start();
require_once 'conn.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch Product
$stmt = $conn->prepare("SELECT * FROM tbl_products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: products.php");
    exit();
}
$product = $result->fetch_assoc();
$stmt->close();

$is_out_of_stock = ($product['product_stock'] === 'out-of-stock');
$stock_text = str_replace('-', ' ', ucwords($product['product_stock']));

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $review_title = trim($_POST['review_title']);
        $review_desc = trim($_POST['review_desc']);
        $review_rating = intval($_POST['review_rating']);
        
        if (!empty($review_title) && !empty($review_desc) && $review_rating >= 1 && $review_rating <= 5) {
            $r_stmt = $conn->prepare("INSERT INTO tbl_reviews (user_id, product_id, review_title, review_desc, review_rating) VALUES (?, ?, ?, ?, ?)");
            // Enums for rating might need string binding or int binding depending on how it's defined.
            // Schema: review_rating enum('1','2','3','4','5'). So bind as string.
            $r_val = strval($review_rating);
            $r_stmt->bind_param("iisss", $user_id, $product_id, $review_title, $review_desc, $r_val);
            if($r_stmt->execute()) {
                header("Location: item.php?id=" . $product_id . "&success=1");
                exit();
            }
            $r_stmt->close();
        }
    }
}

// Fetch Reviews
$reviews_result = $conn->query("SELECT r.*, u.user_name FROM tbl_reviews r JOIN tbl_users u ON r.user_id = u.user_id WHERE r.product_id = $product_id ORDER BY r.review_timestamp DESC");

// Fetch Avg Score
$avg_result = $conn->query("SELECT AVG(review_rating) as avg_rating, COUNT(*) as rev_count FROM tbl_reviews WHERE product_id = $product_id");
$avg_data = $avg_result->fetch_assoc();
$avg_rating = round($avg_data['avg_rating'] ?? 0);
$rev_count = $avg_data['rev_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['product_title']); ?> | Lancashire SU Shop</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .review-section { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 20px; }
    .review { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
    .review-header { display: flex; justify-content: space-between; align-items: baseline; }
    .review-rating { color: #FFD700; }
    .review-form { max-width: 600px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; }
    .review-form input, .review-form textarea, .review-form select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .review-form button { padding: 10px 20px; background: #002366; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .review-form button:hover { background: #001845; }
    .add-to-cart-form { display: inline-block; }
  </style>
</head>
<body class="cloud-bg">
  <header class="primary-red-bg">
    <img src="assets/logo_reverse.png" alt="University of Lancashire Logo" />
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
    <div class="container">
      <div class="single-product">
        <div class="row">
          <div class="col-6">
            <div class="product-image">
              <div class="product-image-main">
                <img src="<?php echo htmlspecialchars($product['product_src']); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>" />
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="product">
              <div class="product-title">
                <h2><?php echo htmlspecialchars($product['product_title']); ?></h2>
              </div>
              
              <div style="margin-bottom: 15px; font-size: 1.2rem;">
                  <?php 
                  for ($i=0; $i<5; $i++) {
                      $color = ($i < $avg_rating) ? '#FFD700' : '#ccc';
                      echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="'.$color.'" stroke="'.$color.'" stroke-width="0.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                  }
                  echo ' <span style="font-size: 1rem; color: #666;">(' . $rev_count . ' reviews)</span>';
                  ?>
              </div>

              <div class="product-price">
                <span class="offer-price">£<?php echo htmlspecialchars($product['product_price']); ?></span>
              </div>
              <div class="product-details">
                <h3><?php echo htmlspecialchars($product['product_desc']); ?></h3>
              </div>
              <div class="product-stock" style="margin: 15px 0;">
                <strong>Availability:</strong> <span class="<?php echo $is_out_of_stock ? 'out' : ''; ?>"><?php echo htmlspecialchars($stock_text); ?></span>
              </div>

              <span class="divider"></span>

              <div class="product-btn-group">
                <form action="cart.php" method="POST" class="add-to-cart-form">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                  <?php if (!isset($_SESSION['user_id'])): ?>
                    <button type="submit" formaction="login.php?msg=login_required" class="button add-cart" <?php echo ($is_out_of_stock ? 'disabled style="opacity:0.5; pointer-events: none;"' : ''); ?>>Add to Cart</button>
                  <?php else: ?>
                    <button type="submit" class="button add-cart" <?php echo ($is_out_of_stock ? 'disabled style="opacity:0.5; pointer-events: none;"' : ''); ?>>Add to Cart</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- REVIEWS SECTION -->
      <div class="review-section">
        <h2>Customer Reviews</h2>
        
        <?php if(isset($_GET['success'])): ?>
            <p style="color: green; font-weight: bold;">Review submitted successfully!</p>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>
          <div class="review-form">
            <h3>Write a Review</h3>
            <form action="item.php?id=<?php echo $product_id; ?>" method="POST">
              <input type="hidden" name="action" value="review">
              <label for="review_title">Title</label>
              <input type="text" id="review_title" name="review_title" required>
              
              <label for="review_rating">Rating</label>
              <select name="review_rating" id="review_rating" required>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
              
              <label for="review_desc">Review Description</label>
              <textarea id="review_desc" name="review_desc" rows="4" required></textarea>
              
              <button type="submit">Submit Review</button>
            </form>
          </div>
        <?php else: ?>
          <p><a href="login.php">Log in</a> to write a review for this product.</p>
        <?php endif; ?>

        <div class="reviews-list">
          <?php if($reviews_result && $reviews_result->num_rows > 0): ?>
            <?php while($rev = $reviews_result->fetch_assoc()): ?>
              <div class="review">
                <div class="review-header">
                  <h4><?php echo htmlspecialchars($rev['review_title']); ?> <small>by <?php echo htmlspecialchars($rev['user_name']); ?></small></h4>
                  <div class="review-rating">
                    <?php 
                    $r = intval($rev['review_rating']);
                    for($i=0; $i<$r; $i++) echo "★";
                    for($i=$r; $i<5; $i++) echo "☆";
                    ?>
                  </div>
                </div>
                <p><?php echo nl2br(htmlspecialchars($rev['review_desc'])); ?></p>
                <small style="color: #999;"><?php echo date('F j, Y, g:i a', strtotime($rev['review_timestamp'])); ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p>No reviews yet. Be the first to review this product!</p>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <p style="text-align:center;margin-top:20px;">
      <a href="products.php">Back to products</a>
    </p>
  </main>
</body>
</html>

