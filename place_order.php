<?php
session_start();
include 'db.php';
$user = $_SESSION['first_name'] ?? 'Guest';

// Fetch products
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NOVEAUX - Shop</title>
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

    .user-name {
      color: #2c2c2c;
      font-weight: 500;
      font-size: 15px;
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

    .hero-section {
      background: linear-gradient(135deg, #2c2c2c 0%, #3a3a3a 100%);
      color: #fff;
      padding: 60px 40px;
      text-align: center;
    }

    .hero-title {
      font-size: 48px;
      font-weight: 300;
      letter-spacing: 4px;
      text-transform: uppercase;
      margin-bottom: 15px;
    }

    .hero-subtitle {
      font-size: 16px;
      font-weight: 400;
      opacity: 0.9;
      letter-spacing: 2px;
    }

    .main-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 60px 40px;
    }

    .section-header {
      margin-bottom: 40px;
    }

    .section-title {
      font-size: 32px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 10px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 35px;
    }

    .product-card {
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }

    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    .product-image {
      width: 100%;
      height: 320px;
      background: #f8f8f8;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      cursor: pointer;
      position: relative;
    }

    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .product-image:hover img {
      transform: scale(1.05);
    }

    .stock-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: #A1BC98;
      color: #fff;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stock-badge.out {
      background: #e74c3c;
    }

    .product-info {
      padding: 25px;
    }

    .product-name {
      font-size: 20px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 12px;
    }

    .product-meta {
      display: flex;
      gap: 20px;
      margin-bottom: 8px;
      font-size: 14px;
      color: #777;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .product-quantity {
      font-size: 13px;
      color: #999;
      margin-bottom: 15px;
    }

    .product-price {
      font-size: 28px;
      font-weight: 700;
      color: #A1BC98;
      margin-bottom: 20px;
    }

    .product-actions {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .add-cart-form {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .quantity-input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 15px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
      transition: border-color 0.3s;
    }

    .quantity-input:focus {
      outline: none;
      border-color: #A1BC98;
    }

    .btn {
      padding: 14px 24px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      font-family: 'Poppins', sans-serif;
    }

    .btn-cart {
      background: #A1BC98;
      color: #fff;
    }

    .btn-cart:hover {
      background: #8fa983;
      transform: translateY(-2px);
    }

    .btn-buy {
      background: #2c2c2c;
      color: #fff;
    }

    .btn-buy:hover {
      background: #3a3a3a;
      transform: translateY(-2px);
    }

    .unavailable-msg {
      background: #ffe5e5;
      color: #c53030;
      padding: 14px;
      border-radius: 8px;
      text-align: center;
      font-weight: 500;
      font-size: 14px;
    }

    /* Image Modal */
    .image-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.95);
    }

    .modal-content {
      position: relative;
      margin: auto;
      padding: 20px;
      width: 90%;
      max-width: 800px;
      top: 50%;
      transform: translateY(-50%);
    }

    .modal-image {
      width: 100%;
      height: auto;
      max-height: 75vh;
      object-fit: contain;
      border-radius: 12px;
    }

    .close {
      position: absolute;
      top: 20px;
      right: 35px;
      color: #fff;
      font-size: 40px;
      font-weight: 300;
      cursor: pointer;
      z-index: 1001;
      transition: opacity 0.3s;
    }

    .close:hover {
      opacity: 0.7;
    }

    .nav-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255,255,255,0.9);
      border: none;
      font-size: 28px;
      padding: 20px;
      cursor: pointer;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
      color: #2c2c2c;
    }

    .nav-button:hover {
      background: #A1BC98;
      color: #fff;
    }

    .nav-button.prev {
      left: 30px;
    }

    .nav-button.next {
      right: 30px;
    }

    .image-counter {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      color: #fff;
      background: rgba(0,0,0,0.7);
      padding: 10px 20px;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 500;
    }

    /* Buy Now Modal */
    .buy-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      overflow-y: auto;
    }

    .buy-modal-content {
      background: #fff;
      margin: 5% auto;
      padding: 0;
      border-radius: 16px;
      width: 90%;
      max-width: 900px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      position: relative;
    }

    .buy-modal-close {
      position: absolute;
      top: 20px;
      right: 25px;
      color: #999;
      font-size: 32px;
      font-weight: 300;
      cursor: pointer;
      z-index: 10;
      transition: color 0.3s;
    }

    .buy-modal-close:hover {
      color: #2c2c2c;
    }

    .buy-modal-header {
      padding: 30px 40px 20px;
      border-bottom: 1px solid #e8e8e8;
    }

    .buy-modal-title {
      font-size: 28px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 8px;
    }

    .buy-modal-body {
      padding: 30px 40px;
    }

    .buy-product-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .buy-details-left h2 {
      font-size: 24px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 20px;
    }

    .buy-details {
      background: #f8f8f8;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .buy-details p {
      font-size: 15px;
      color: #555;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .buy-details p:last-child {
      margin-bottom: 0;
    }

    .buy-details strong {
      color: #2c2c2c;
      font-weight: 600;
      min-width: 60px;
    }

    .buy-price-tag {
      font-size: 36px;
      font-weight: 700;
      color: #A1BC98;
      margin-bottom: 25px;
    }

    .buy-product-pics {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 15px;
    }

    .buy-product-pics img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 12px;
      border: 2px solid #e8e8e8;
      cursor: pointer;
      transition: all 0.3s;
    }

    .buy-product-pics img:hover {
      border-color: #A1BC98;
      transform: scale(1.05);
    }

    .buy-modal-footer {
      padding: 25px 40px;
      background: #f8f8f8;
      border-top: 1px solid #e8e8e8;
      display: flex;
      justify-content: flex-end;
      gap: 15px;
    }

    .btn-secondary {
      background: transparent;
      border: 2px solid #2c2c2c;
      color: #2c2c2c;
    }

    .btn-secondary:hover {
      background: #2c2c2c;
      color: #fff;
    }

    .btn-confirm {
      background: #A1BC98;
      color: #fff;
    }

    .btn-confirm:hover {
      background: #8fa983;
    }

    /* Success Message */
    .success-message {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 40px 50px;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      z-index: 2000;
      text-align: center;
      min-width: 400px;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      background: #A1BC98;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 40px;
      color: #fff;
    }

    .success-message h3 {
      font-size: 24px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 10px;
    }

    .success-message p {
      font-size: 15px;
      color: #777;
      margin-bottom: 25px;
    }

    .success-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1999;
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

      .user-section {
        flex-direction: column;
        gap: 10px;
      }

      .hero-title {
        font-size: 32px;
      }

      .main-content {
        padding: 40px 20px;
      }

      .products-grid {
        grid-template-columns: 1fr;
        gap: 25px;
      }

      .nav-button {
        width: 45px;
        height: 45px;
        font-size: 20px;
        padding: 12px;
      }

      .nav-button.prev {
        left: 15px;
      }

      .nav-button.next {
        right: 15px;
      }

      .buy-product-details {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .buy-modal-header,
      .buy-modal-body,
      .buy-modal-footer {
        padding: 20px;
      }

      .buy-modal-title {
        font-size: 22px;
      }

      .buy-modal-footer {
        flex-direction: column;
      }

      .success-message {
        min-width: 90%;
        padding: 30px 25px;
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
        <a href="home.php" class="active">Shop</a>
        <a href="my_cart.php">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="my_reviews.php">Reviews</a>
      </div>

      <div class="user-section">
        <a href="logout.php" class="logout-btn">Sign Out</a>
      </div>
    </div>
  </nav>

  <div class="hero-section">
    <h1 class="hero-title">New Collection</h1>
    <p class="hero-subtitle">Discover Your Style</p>
  </div>

  <div class="main-content">
    <div class="section-header">
      <h2 class="section-title">Featured Products</h2>
    </div>

    <div class="products-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <div class="product-image" onclick="openModal(<?php echo $product['id']; ?>, 0)">
            <?php 
            $images = [];
            if (!empty($product['image1'])) $images[] = $product['image1'];
            if (!empty($product['image2'])) $images[] = $product['image2'];
            if (!empty($product['image3'])) $images[] = $product['image3'];
            
            if (!empty($images)): ?>
              <img src="uploads/<?php echo basename($images[0]); ?>" 
                   alt="<?php echo htmlspecialchars($product['name']); ?>">
              <div class="stock-badge <?php echo $product['availability'] !== 'Available' ? 'out' : ''; ?>">
                <?php echo $product['availability'] === 'Available' ? 'In Stock' : 'Out of Stock'; ?>
              </div>
            <?php else: ?>
              <div style="color: #999;">No Image Available</div>
            <?php endif; ?>
          </div>

          <div class="product-info">
            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
            <div class="product-meta">
              <span class="meta-item">Size: <?php echo htmlspecialchars($product['size']); ?></span>
              <span class="meta-item">Color: <?php echo htmlspecialchars($product['color']); ?></span>
            </div>
            <div class="product-quantity"><?php echo htmlspecialchars($product['quantity'] ?? ''); ?></div>
            <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>

            <div class="product-actions">
              <?php if ($product['availability'] === 'Available'): ?>
                <form class="add-cart-form" action="add_to_cart.php" method="POST">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="number" name="quantity" value="1" min="1" required class="quantity-input" placeholder="Quantity">
                  <button type="submit" class="btn btn-cart">Add to Cart</button>
                </form>
                <button onclick="openBuyModal(<?php echo $product['id']; ?>)" class="btn btn-buy">Buy Now</button>
              <?php else: ?>
                <div class="unavailable-msg">Currently Unavailable</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Image Modal -->
  <div id="imageModal" class="image-modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <img id="modalImage" class="modal-image" src="" alt="Product Image">
      <button class="nav-button prev" onclick="changeImage(-1)">&#10094;</button>
      <button class="nav-button next" onclick="changeImage(1)">&#10095;</button>
      <div class="image-counter">
        <span id="currentImageNum">1</span> / <span id="totalImages">1</span>
      </div>
    </div>
  </div>

  <!-- Buy Now Modal -->
  <div id="buyModal" class="buy-modal">
    <div class="buy-modal-content">
      <span class="buy-modal-close" onclick="closeBuyModal()">&times;</span>
      <div class="buy-modal-header">
        <div class="buy-modal-title">Product Details</div>
      </div>
      <div class="buy-modal-body">
        <div class="buy-product-details">
          <div class="buy-details-left">
            <h2 id="buyProductName"></h2>
            <div class="buy-details">
              <p><strong>Size:</strong> <span id="buyProductSize"></span></p>
              <p><strong>Color:</strong> <span id="buyProductColor"></span></p>
              <p><strong>Stock:</strong> <span id="buyProductQuantity"></span></p>
            </div>
            <div class="buy-price-tag" id="buyProductPrice"></div>
          </div>
          <div class="buy-details-right">
            <div class="buy-product-pics" id="buyProductImages">
              <!-- Images will be inserted here -->
            </div>
          </div>
        </div>
      </div>
      <div class="buy-modal-footer">
        <button class="btn btn-secondary" onclick="closeBuyModal()">Cancel</button>
        <button class="btn btn-confirm" id="confirmBuyBtn">Proceed to Checkout</button>
      </div>
    </div>
  </div>

  <!-- Success Message -->
  <div class="success-overlay" id="successOverlay"></div>
  <div class="success-message" id="successMessage">
    <div class="success-icon">✓</div>
    <h3>Order Checked Out!</h3>
    <p>Your order has been successfully placed.</p>
    <button class="btn btn-confirm" onclick="closeSuccessMessage()">Continue Shopping</button>
  </div>

  <script>
    let currentProductId = null;
    let currentImageIndex = 0;
    let productImages = {};
    let productsData = {};

    document.addEventListener('DOMContentLoaded', function() {
      <?php foreach ($products as $product): ?>
        productImages[<?php echo $product['id']; ?>] = [
          <?php 
          $images = [];
          if (!empty($product['image1'])) $images[] = "'" . basename($product['image1']) . "'";
          if (!empty($product['image2'])) $images[] = "'" . basename($product['image2']) . "'";
          if (!empty($product['image3'])) $images[] = "'" . basename($product['image3']) . "'";
          echo implode(', ', $images);
          ?>
        ];
        
        productsData[<?php echo $product['id']; ?>] = {
          name: <?php echo json_encode($product['name']); ?>,
          size: <?php echo json_encode($product['size']); ?>,
          color: <?php echo json_encode($product['color']); ?>,
          quantity: <?php echo json_encode($product['quantity'] ?? 'N/A'); ?>,
          price: <?php echo $product['price']; ?>
        };
      <?php endforeach; ?>
    });

    // Image Modal Functions
    function openModal(productId, imageIndex) {
      currentProductId = productId;
      currentImageIndex = imageIndex;
      
      const modal = document.getElementById('imageModal');
      const modalImage = document.getElementById('modalImage');
      const currentNum = document.getElementById('currentImageNum');
      const totalNum = document.getElementById('totalImages');
      
      const images = productImages[productId];
      if (images && images.length > 0) {
        modalImage.src = 'uploads/' + images[imageIndex];
        currentNum.textContent = imageIndex + 1;
        totalNum.textContent = images.length;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
      }
    }

    function closeModal() {
      const modal = document.getElementById('imageModal');
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    function changeImage(direction) {
      if (currentProductId === null) return;
      
      const images = productImages[currentProductId];
      if (!images || images.length === 0) return;
      
      currentImageIndex += direction;
      
      if (currentImageIndex >= images.length) {
        currentImageIndex = 0;
      } else if (currentImageIndex < 0) {
        currentImageIndex = images.length - 1;
      }
      
      const modalImage = document.getElementById('modalImage');
      const currentNum = document.getElementById('currentImageNum');
      
      modalImage.src = 'uploads/' + images[currentImageIndex];
      currentNum.textContent = currentImageIndex + 1;
    }

    // Buy Modal Functions
    function openBuyModal(productId) {
      const product = productsData[productId];
      const images = productImages[productId];
      
      if (!product) return;
      
      // Populate product details
      document.getElementById('buyProductName').textContent = product.name;
      document.getElementById('buyProductSize').textContent = product.size;
      document.getElementById('buyProductColor').textContent = product.color;
      document.getElementById('buyProductQuantity').textContent = product.quantity;
      document.getElementById('buyProductPrice').textContent = '₱' + parseFloat(product.price).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      
      // Populate images
      const imagesContainer = document.getElementById('buyProductImages');
      imagesContainer.innerHTML = '';
      
      if (images && images.length > 0) {
        images.forEach((img, index) => {
          const imgElement = document.createElement('img');
          imgElement.src = 'uploads/' + img;
          imgElement.alt = 'Product Image ' + (index + 1);
          imgElement.onclick = function() {
            openModal(productId, index);
          };
          imagesContainer.appendChild(imgElement);
        });
      }
      
      // Set up confirm button
      document.getElementById('confirmBuyBtn').onclick = function() {
        processCheckout(productId);
      };
      
      // Show modal
      document.getElementById('buyModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeBuyModal() {
      document.getElementById('buyModal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    // Checkout Process
    function processCheckout(productId) {
      // Create form data
      const formData = new FormData();
      formData.append('buy_now', '1');
      formData.append('product_id', productId);
      formData.append('quantity', '1');

      // Send AJAX request
      fetch('place_order.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // Close buy modal
        closeBuyModal();
        
        // Show success message
        showSuccessMessage();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
    }

    function showSuccessMessage() {
      document.getElementById('successOverlay').style.display = 'block';
      document.getElementById('successMessage').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeSuccessMessage() {
      document.getElementById('successOverlay').style.display = 'none';
      document.getElementById('successMessage').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    // Close modals when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    document.getElementById('buyModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeBuyModal();
      }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      const imageModal = document.getElementById('imageModal');
      const buyModal = document.getElementById('buyModal');
      
      if (imageModal.style.display === 'block') {
        if (e.key === 'Escape') {
          closeModal();
        } else if (e.key === 'ArrowLeft') {
          changeImage(-1);
        } else if (e.key === 'ArrowRight') {
          changeImage(1);
        }
      }
      
      if (buyModal.style.display === 'block' && e.key === 'Escape') {
        closeBuyModal();
      }
    });
  </script>
</body>
</html>