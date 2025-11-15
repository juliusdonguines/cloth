<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $price = $_POST['price'];

    // Check if at least one photo is uploaded
    $hasAtLeastOnePhoto = false;
    for ($i = 1; $i <= 3; $i++) {
        $fileKey = "photo$i";
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
            $hasAtLeastOnePhoto = true;
            break;
        }
    }

    if (!$hasAtLeastOnePhoto) {
        echo "<script>alert('Please upload at least one product image.'); history.back();</script>";
        exit;
    }

    $uploadDir = 'uploads/';
    // Initialize array with empty strings for all 3 positions
    $imagePaths = ['', '', ''];

    for ($i = 1; $i <= 3; $i++) {
        $fileKey = "photo$i";
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === 0) {
            $fileTmp = $_FILES[$fileKey]['tmp_name'];
            $fileName = time() . "_$i_" . basename($_FILES[$fileKey]['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $imagePaths[$i-1] = $targetPath; // Store in correct position (0, 1, 2)
            } else {
                echo "<script>alert('Failed to upload image $i.'); history.back();</script>";
                exit;
            }
        }
        // If no file uploaded, $imagePaths[$i-1] remains empty string
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (name, size, color, price, image1, image2, image3) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $size, $color, $price, $imagePaths[0], $imagePaths[1], $imagePaths[2]);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to add product.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Add Product</title>
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
            color: #2c2c2c;
            line-height: 1.6;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #e8e8e8;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        .logo {
            padding: 0 30px 30px;
            border-bottom: 2px solid #e8e8e8;
            margin-bottom: 30px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
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

        .logo h2 {
            font-size: 16px;
            font-weight: 500;
            color: #777;
            margin-top: 5px;
        }

        .nav-menu {
            padding: 0 15px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            text-decoration: none;
            color: #555;
            border-radius: 10px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: #f8f8f8;
            color: #A1BC98;
        }

        .nav-link.active {
            background-color: #e8f3e5;
            color: #A1BC98;
        }

        .logout-section {
            margin-top: auto;
            padding: 30px 15px 0;
            border-top: 2px solid #e8e8e8;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 14px 20px;
            background-color: #2c2c2c;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #A1BC98;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
        }

        /* Header */
        .admin-header {
            background: linear-gradient(135deg, #2c2c2c 0%, #3a3a3a 100%);
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 40px;
        }

        .admin-title {
            color: #ffffff;
            font-size: 42px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
            line-height: 1.2;
        }

        .admin-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .content-wrapper {
            padding: 0 40px 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            padding: 40px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background-color: #fff;
            transition: border-color 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #A1BC98;
        }

        .form-input::placeholder {
            color: #999;
        }

        .file-input-group {
            margin-bottom: 30px;
        }

        .file-input-label {
            display: block;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .file-inputs {
            display: grid;
            gap: 15px;
        }

        .file-input-wrapper {
            position: relative;
        }

        .file-upload-box {
            border: 2px dashed #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background-color: #f8f8f8;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .file-upload-box:hover {
            border-color: #A1BC98;
            background-color: #f0f0f0;
        }

        .file-upload-box.has-image {
            border-color: #A1BC98;
            background-color: #e8f3e5;
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-preview {
            display: none;
            margin-top: 10px;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .file-upload-text {
            font-size: 14px;
            color: #777;
        }

        .file-upload-icon {
            font-size: 32px;
            color: #A1BC98;
            margin-bottom: 10px;
        }

        .file-helper {
            font-size: 13px;
            color: #999;
            margin-top: 8px;
        }

        .submit-btn {
            width: 100%;
            background-color: #A1BC98;
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .submit-btn:hover {
            background-color: #8fa983;
            transform: translateY(-2px);
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 0 20px 20px;
            }

            .admin-title {
                font-size: 28px;
                letter-spacing: 2px;
            }

            .form-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-section">
                <div class="logo-mark">N</div>
                <div class="logo-text">NOVEAUX</div>
            </div>
            <h2>Admin Panel</h2>
        </div>

        <nav class="nav-menu">
            <div class="nav-item">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            </div>
            <div class="nav-item">
                <a href="product.php" class="nav-link active">Products</a>
            </div>
            <div class="nav-item">
                <a href="my_orders.php" class="nav-link">Orders</a>
            </div>
            <div class="nav-item">
                <a href="customers.php" class="nav-link">Customers</a>
            </div>
            <div class="nav-item">
                <a href="statistics.php" class="nav-link">Statistics</a>
            </div>
            <div class="nav-item">
                <a href="admin_reviews.php" class="nav-link">Reviews</a>
            </div>
        </nav>
        
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="admin-header">
            <h1 class="admin-title">Add New Product</h1>
            <p class="admin-subtitle">Fill in the details for your new product</p>
        </div>

        <div class="content-wrapper">
            <div class="form-card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name" class="form-label">Product Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Enter product name" required>
                    </div>

                    <div class="form-group">
                        <label for="size" class="form-label">Size <span class="required">*</span></label>
                        <input type="text" id="size" name="size" class="form-input" placeholder="S, M, L, XL" required>
                    </div>

                    <div class="form-group">
                        <label for="color" class="form-label">Color <span class="required">*</span></label>
                        <input type="text" id="color" name="color" class="form-input" placeholder="Enter color" required>
                    </div>

                    <div class="form-group">
                        <label for="price" class="form-label">Price (₱) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                    </div>

                    <div class="file-input-group">
                        <label class="file-input-label">Product Images <span class="required">*</span></label>
                        <div class="file-inputs">
                            <div class="file-input-wrapper">
                                <div class="file-upload-box" id="upload-box-1">
                                    <div class="file-upload-icon">📷</div>
                                    <div class="file-upload-text">Click to upload primary image</div>
                                    <input type="file" name="photo1" class="file-input" accept="image/*" required onchange="previewImage(this, 1)">
                                    <div class="file-preview" id="preview-1"></div>
                                </div>
                                <div class="file-helper">Primary image (required)</div>
                            </div>
                            <div class="file-input-wrapper">
                                <div class="file-upload-box" id="upload-box-2">
                                    <div class="file-upload-icon">📷</div>
                                    <div class="file-upload-text">Click to upload secondary image</div>
                                    <input type="file" name="photo2" class="file-input" accept="image/*" onchange="previewImage(this, 2)">
                                    <div class="file-preview" id="preview-2"></div>
                                </div>
                                <div class="file-helper">Secondary image (optional)</div>
                            </div>
                            <div class="file-input-wrapper">
                                <div class="file-upload-box" id="upload-box-3">
                                    <div class="file-upload-icon">📷</div>
                                    <div class="file-upload-text">Click to upload additional image</div>
                                    <input type="file" name="photo3" class="file-input" accept="image/*" onchange="previewImage(this, 3)">
                                    <div class="file-preview" id="preview-3"></div>
                                </div>
                                <div class="file-helper">Additional image (optional)</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Add Product</button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    function previewImage(input, index) {
        const box = document.getElementById('upload-box-' + index);
        const preview = document.getElementById('preview-' + index);
        const uploadText = box.querySelector('.file-upload-text');
        const uploadIcon = box.querySelector('.file-upload-icon');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                preview.style.display = 'block';
                uploadText.textContent = 'Image uploaded - Click to change';
                uploadIcon.textContent = '✓';
                uploadIcon.style.color = '#A1BC98';
                box.classList.add('has-image');
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>