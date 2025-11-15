<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['product_id'])) {
    echo "No product selected.";
    exit();
}

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Buy Now</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fafafa;
      margin: 0;
      padding: 20px;
    }

    .container {
      background: white;
      max-width: 480px;
      margin: 20px auto;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border: 1px solid #e5e5e5;
    }

    .hot-deal {
      background: #ff4757;
      color: white;
      text-align: center;
      padding: 8px;
      margin: -25px -25px 20px -25px;
      font-size: 14px;
      font-weight: bold;
    }

    h1 {
      color: #333;
      margin-bottom: 15px;
      font-size: 24px;
    }

    .details {
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .details p {
      margin: 8px 0;
      color: #666;
    }

    .price-tag {
      font-size: 28px;
      color: #e74c3c;
      font-weight: bold;
      margin: 15px 0;
    }

    .product-pics {
      display: flex;
      gap: 12px;
      margin: 18px 0;
    }

    .product-pics img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 6px;
      border: 2px solid #ddd;
    }

    .buy-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 6px;
      margin-top: 20px;
      border: 1px solid #dee2e6;
    }

    .qty-label {
      font-weight: bold;
      margin-bottom: 8px;
      display: block;
      color: #555;
    }

    .qty-input {
      width: 70px;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    .purchase-btn {
      background: #28a745;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
    }

    .purchase-btn:hover {
      background: #218838;
    }

    .stock-warning {
      color: #dc3545;
      font-size: 13px;
      margin-top: 10px;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="hot-deal">HOT DEAL - LIMITED TIME</div>
  
  <h1><?php echo htmlspecialchars($product['name']); ?></h1>

  <div class="details">
    <p>Size: <?php echo htmlspecialchars($product['size']); ?></p>
    <p>Color: <?php echo htmlspecialchars($product['color']); ?></p>
  </div>

  <div class="price-tag">₱<?php echo number_format($product['price'], 2); ?></div>

  <div class="product-pics">
    <?php
      for ($i = 1; $i <= 3; $i++) {
          $imgField = 'image' . $i;
          $imgFile = $product[$imgField] ?? '';
          $imgPath = 'uploads/' . basename($imgFile);

          if (!empty($imgFile) && file_exists($imgPath)) {
              echo '<img src="' . htmlspecialchars($imgPath) . '" alt="Product">';
          }
      }
    ?>
  </div>

  <form action="place_order.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
    
    <div class="buy-section">

      <button type="submit" name="buy_now" class="purchase-btn">BUY NOW</button>
      
    </div>
  </form>
</div>

</body>
</html>