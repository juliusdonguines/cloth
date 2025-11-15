<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $_SESSION['first_name'] ?? 'Guest';

$sql = "SELECT c.id AS cart_id, c.quantity, p.name, p.price, p.image1, p.id as product_id
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NOVEAUX - Shopping Cart</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fafafa;
      line-height: 1.6;
    }

    .top-bar {
      background: #2c2c2c;
      color: #fff;
      padding: 12px 0;
      text-align: center;
      font-size: 13px;
      letter-spacing: 0.5px;
    }

    .navbar {
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-mark {
      width: 45px;
      height: 45px;
      background: #A1BC98;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700;
      font-size: 20px;
    }

    .logo-text {
      font-size: 24px;
      font-weight: 700;
      color: #2c2c2c;
      letter-spacing: 1px;
    }

    .nav-links {
      display: flex;
      gap: 35px;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: #555;
      font-weight: 500;
      font-size: 15px;
      transition: color 0.3s;
      position: relative;
    }

    .nav-links a:hover {
      color: #A1BC98;
    }

    .nav-links a.active {
      color: #A1BC98;
    }

    .nav-links a.active::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      right: 0;
      height: 2px;
      background: #A1BC98;
    }

    .user-section {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .logout-btn {
      padding: 10px 24px;
      background: #2c2c2c;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
    }

    .logout-btn:hover {
      background: #A1BC98;
    }

    .main-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 40px;
    }

    .page-header {
      margin-bottom: 40px;
    }

    .page-title {
      font-size: 36px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 8px;
    }

    .breadcrumb {
      color: #777;
      font-size: 14px;
    }

    .breadcrumb a {
      color: #A1BC98;
      text-decoration: none;
    }

    .cart-layout {
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 30px;
      align-items: start;
    }

    .cart-items-section {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    .section-header {
      padding: 25px 30px;
      background: #f8f8f8;
      border-bottom: 1px solid #e8e8e8;
    }

    .section-title {
      font-size: 20px;
      font-weight: 600;
      color: #2c2c2c;
    }

    .select-all-section {
      padding: 20px 30px;
      background: #fafafa;
      border-bottom: 1px solid #e8e8e8;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .select-all-section input[type="checkbox"] {
      width: 20px;
      height: 20px;
      cursor: pointer;
      accent-color: #A1BC98;
    }

    .select-all-section label {
      font-weight: 500;
      color: #2c2c2c;
      cursor: pointer;
      font-size: 15px;
    }

    .cart-items {
      padding: 0;
    }

    .cart-item {
      display: flex;
      align-items: center;
      padding: 25px 30px;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.3s;
    }

    .cart-item:hover {
      background-color: #fafafa;
    }

    .cart-item:last-child {
      border-bottom: none;
    }

    .item-checkbox {
      margin-right: 20px;
    }

    .item-checkbox input[type="checkbox"] {
      width: 20px;
      height: 20px;
      cursor: pointer;
      accent-color: #A1BC98;
    }

    .item-image {
      width: 90px;
      height: 90px;
      margin-right: 20px;
      border-radius: 12px;
      overflow: hidden;
      flex-shrink: 0;
      background: #f8f8f8;
    }

    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .item-details {
      flex-grow: 1;
      margin-right: 20px;
    }

    .item-name {
      font-weight: 600;
      font-size: 16px;
      color: #2c2c2c;
      margin-bottom: 6px;
    }

    .item-info {
      color: #999;
      font-size: 13px;
    }

    .item-quantity {
      margin-right: 30px;
      text-align: center;
      min-width: 80px;
    }

    .quantity-label {
      font-size: 12px;
      color: #999;
      margin-bottom: 4px;
      display: block;
    }

    .quantity-value {
      font-weight: 600;
      font-size: 18px;
      color: #2c2c2c;
      background: #f8f8f8;
      padding: 6px 12px;
      border-radius: 8px;
      display: inline-block;
    }

    .item-price {
      text-align: right;
      min-width: 130px;
    }

    .unit-price {
      font-size: 13px;
      color: #999;
      margin-bottom: 4px;
    }

    .total-price {
      font-weight: 700;
      font-size: 18px;
      color: #A1BC98;
    }

    .order-summary {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
      padding: 30px;
      position: sticky;
      top: 100px;
    }

    .summary-title {
      font-size: 20px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      font-size: 15px;
    }

    .summary-label {
      color: #777;
    }

    .summary-value {
      font-weight: 600;
      color: #2c2c2c;
    }

    .summary-divider {
      height: 1px;
      background: #e8e8e8;
      margin: 20px 0;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
      font-size: 18px;
      font-weight: 700;
    }

    .total-label {
      color: #2c2c2c;
    }

    .total-value {
      color: #A1BC98;
      font-size: 24px;
    }

    .checkout-btn {
      width: 100%;
      padding: 16px;
      background: #A1BC98;
      border: none;
      border-radius: 10px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 25px;
      font-family: 'Poppins', sans-serif;
    }

    .checkout-btn:hover:not(:disabled) {
      background: #8fa983;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(161, 188, 152, 0.3);
    }

    .checkout-btn:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
    }

    .continue-shopping {
      width: 100%;
      padding: 16px;
      background: transparent;
      border: 2px solid #2c2c2c;
      border-radius: 10px;
      color: #2c2c2c;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 12px;
      text-decoration: none;
      display: block;
      text-align: center;
      font-family: 'Poppins', sans-serif;
    }

    .continue-shopping:hover {
      background: #2c2c2c;
      color: #fff;
    }

    .empty-cart {
      background: #fff;
      border-radius: 16px;
      padding: 80px 40px;
      text-align: center;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }

    .empty-icon {
      font-size: 80px;
      margin-bottom: 20px;
      opacity: 0.3;
    }

    .empty-cart h3 {
      font-size: 24px;
      color: #2c2c2c;
      margin-bottom: 12px;
    }

    .empty-cart p {
      color: #999;
      margin-bottom: 30px;
      font-size: 15px;
    }

    .shop-btn {
      display: inline-block;
      padding: 14px 32px;
      background: #A1BC98;
      color: #fff;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s;
    }

    .shop-btn:hover {
      background: #8fa983;
      transform: translateY(-2px);
    }

    @media (max-width: 1024px) {
      .cart-layout {
        grid-template-columns: 1fr;
      }

      .order-summary {
        position: relative;
        top: 0;
      }
    }

    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        gap: 20px;
        padding: 20px;
      }

      .nav-links {
        flex-direction: column;
        gap: 15px;
        width: 100%;
      }

      .main-content {
        padding: 20px;
      }

      .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        padding: 20px;
      }

      .item-image {
        align-self: center;
        margin-right: 0;
      }

      .item-quantity,
      .item-price {
        margin-right: 0;
        text-align: left;
        min-width: auto;
      }
    }
  </style>
</head>
<body>
  <div class="top-bar">
    Welcome, <?php echo htmlspecialchars($user); ?> | Free Shipping on Orders Over ₱1,000
  </div>

  <nav class="navbar">
    <div class="nav-container">
      <div class="logo-section">
        <div class="logo-mark">N</div>
        <div class="logo-text">NOVEAUX</div>
      </div>

      <div class="nav-links">
        <a href="home.php">Shop</a>
        <a href="my_cart.php" class="active">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="my_reviews.php">Reviews</a>
      </div>

      <div class="user-section">
        <a href="logout.php" class="logout-btn">Sign Out</a>
      </div>
    </div>
  </nav>

  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Shopping Cart</h1>
      <div class="breadcrumb">
        <a href="home.php">Home</a> / Cart
      </div>
    </div>

    <?php if (count($cart_items) > 0): ?>
      <form action="place_order.php" method="POST" id="cartForm">
        <div class="cart-layout">
          <div class="cart-items-section">
            <div class="section-header">
              <h2 class="section-title">Cart Items (<?php echo count($cart_items); ?>)</h2>
            </div>

            <div class="select-all-section">
              <input type="checkbox" id="selectAll">
              <label for="selectAll">Select all items</label>
            </div>

            <div class="cart-items">
              <?php foreach ($cart_items as $item): ?>
                <?php
                  $total = $item['price'] * $item['quantity'];
                  $cart_id = $item['cart_id'];
                ?>
                <div class="cart-item" data-price="<?php echo $total; ?>">
                  <div class="item-checkbox">
                    <input type="checkbox" name="cart_ids[]" value="<?php echo $cart_id; ?>" class="item-select">
                    <input type="hidden" name="product_ids[<?php echo $cart_id; ?>]" value="<?php echo $item['product_id']; ?>">
                    <input type="hidden" name="quantities[<?php echo $cart_id; ?>]" value="<?php echo $item['quantity']; ?>">
                  </div>
                  
                  <div class="item-image">
                    <img src="uploads/<?php echo basename($item['image1']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                  </div>
                  
                  <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="item-info">Ready to ship</div>
                  </div>
                  
                  <div class="item-quantity">
                    <span class="quantity-label">Qty</span>
                    <div class="quantity-value"><?php echo $item['quantity']; ?></div>
                  </div>
                  
                  <div class="item-price">
                    <div class="unit-price">₱<?php echo number_format($item['price'], 2); ?> each</div>
                    <div class="total-price">₱<?php echo number_format($total, 2); ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="order-summary">
            <h3 class="summary-title">Order Summary</h3>
            
            <div class="summary-row">
              <span class="summary-label">Selected Items</span>
              <span class="summary-value" id="itemCount">0</span>
            </div>
            
            <div class="summary-row">
              <span class="summary-label">Subtotal</span>
              <span class="summary-value" id="subtotal">₱0.00</span>
            </div>
            
            <div class="summary-row">
              <span class="summary-label">Shipping</span>
              <span class="summary-value">Free</span>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-total">
              <span class="total-label">Total</span>
              <span class="total-value" id="grandTotal">₱0.00</span>
            </div>

            <button type="submit" class="checkout-btn" id="placeOrderBtn" disabled>Select items to checkout</button>
            <a href="home.php" class="continue-shopping">Continue Shopping</a>
          </div>
        </div>
      </form>
    <?php else: ?>
      <div class="empty-cart">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything to your cart yet.</p>
        <a href="home.php" class="shop-btn">Start Shopping</a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-select');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const itemCountElement = document.getElementById('itemCount');
    const subtotalElement = document.getElementById('subtotal');
    const grandTotalElement = document.getElementById('grandTotal');

    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateOrderSummary();
      });

      itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          const checkedItems = document.querySelectorAll('.item-select:checked').length;
          selectAllCheckbox.checked = checkedItems === itemCheckboxes.length;
          selectAllCheckbox.indeterminate = checkedItems > 0 && checkedItems < itemCheckboxes.length;
          updateOrderSummary();
        });
      });

      function updateOrderSummary() {
        const checkedItems = document.querySelectorAll('.item-select:checked');
        let total = 0;

        if (checkedItems.length > 0) {
          checkedItems.forEach(checkbox => {
            const cartItem = checkbox.closest('.cart-item');
            const price = parseFloat(cartItem.getAttribute('data-price'));
            total += price;
          });
          
          itemCountElement.textContent = checkedItems.length;
          subtotalElement.textContent = `₱${total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
          grandTotalElement.textContent = `₱${total.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
          
          placeOrderBtn.textContent = `Proceed to Checkout (${checkedItems.length})`;
          placeOrderBtn.disabled = false;
        } else {
          itemCountElement.textContent = '0';
          subtotalElement.textContent = '₱0.00';
          grandTotalElement.textContent = '₱0.00';
          placeOrderBtn.textContent = 'Select items to checkout';
          placeOrderBtn.disabled = true;
        }
      }

      updateOrderSummary();
    }

    document.getElementById('cartForm')?.addEventListener('submit', function(e) {
      const checkedItems = document.querySelectorAll('.item-select:checked').length;
      if (checkedItems === 0) {
        e.preventDefault();
        alert('Please select at least one item to place an order.');
      }
    });
  </script>
</body>
</html>