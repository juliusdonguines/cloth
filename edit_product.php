<?php
// edit_product.php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Invalid product ID.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    
    $stmt = $conn->prepare("UPDATE products SET name=?, size=?, color=?, price=?, availability=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $size, $color, $price, $availability, $id);
    $stmt->execute();
    
    header("Location: admin_dashboard.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #333;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        select {
            cursor: pointer;
        }
        
        .btn-group {
            text-align: center;
            margin-top: 30px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            text-decoration: none;
            display: inline-block;
            padding: 12px 30px;
            color: white;
            border-radius: 4px;
        }
        
        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" required>
            </div>

            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($product['color']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="form-group">
                <label for="availability">Availability</label>
                <select id="availability" name="availability">
                    <option value="Available" <?php if ($product['availability'] === 'Available') echo 'selected'; ?>>Available</option>
                    <option value="Not Available" <?php if ($product['availability'] === 'Not Available') echo 'selected'; ?>>Not Available</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit">Update Product</button>
                <a href="admin_dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>