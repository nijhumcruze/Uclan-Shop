# Assignment 2 - E-Commerce Backend Extension

**Student Name:** Nijhum Nicholas Cruzer
**Student ID:** G21315956
**Homepage URL:** 
**Video Demo URL:** 

## Features Implemented
This submission successfully extends the Assignment 1 static site into a dynamic PHP application connecting to a MySQL database (`assignment2.sql` schema), aiming for an **80+ (First) grade**.

### 1. Database Connectivity & Security
- `conn.php` handles secure `mysqli` database connection.
- Prepared statements (`bind_param`) are used for all `INSERT` and `SELECT` queries where user input is involved to prevent SQL Injection.
- Handled XSS protection by sanitizing output strings with `htmlspecialchars()`.
- Passwords are encrypted in the database using strong `bcrypt` hashing.

### 2. User Authentication
A complete login and registration flow is provided. Sessions are utilized to govern access to specific actions:
- Guests are redirected to `login.php` if they attempt to checkout.
- Logged-in users see a personalized welcome message on the Navigation bar and can access the logout securely.

### 3. Dynamic Product and Offer Displays
- **Homepage (`index.php`)**: Queries `tbl_offers` to present the current active offers dynamically using PHP logic.
- **Products Page (`products.php`)**: Fetches all merchandise from `tbl_products`, accurately reflecting inventory, current prices, and dynamically enabling/disabling the Add to Cart button based on the stock availability.
- **Item Page (`item.php`)**: Converted from localStorage/JS architecture to utilise PHP `$_GET` variables. It queries product details securely. It also fetches and calculates the average review score dynamically from `tbl_reviews`.

### 4. Reviews System
- Authenticated users can submit a review with a rating (1 to 5) straight from the `item.php` page. 
- The form is hidden from guests, prompting them to log in. Submitted reviews are inserted into `tbl_reviews` securely.

### 5. Cart functionality & Checkout
- The cart was fully rewritten to utilise **PHP Cookies** exclusively. It stores tracking identifiers securely across browser sessions.
- Cart reads data from the cookies and cross-references it with LIVE database pricing to prevent client-side manipulation.
- Applies dynamic discount conditions (Promo Code: `GRAD25` provides exactly 25% off).
- **Checkout**: Safely creates a string array of `product_ids` and injects an order into `tbl_orders` upon successful validation, before flushing the PHP cart cookie.

## Testing & Verification
### Dummy Account Details
To rapidly test the restricted components (Reviewing and Checkout):
- **Email:** mbates5@lancashire.ac.uk
- **Password:** password123 
*(Note: As per initial DB schema, you can also register a new account on `register.php` and it will securely hash your chosen password).*

*(Don't forget to include your demonstration video link at the top!).*
