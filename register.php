<?php
session_start();
$host = "localhost";
$dbname = "user_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $contact_number = $_POST["contact_number"];
    $birthday = $_POST["birthday"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, address, contact_number, birthday, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $address, $contact_number, $birthday, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['email'] = $email;
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>NOVEAUX - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('uploads/unnamed.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 40px 20px;
            position: relative;
            overflow-y: auto;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .register-container {
            width: 100%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-header h2 {
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .register-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
        }

        .error-message {
            background: rgba(255, 107, 107, 0.2);
            border-left: 4px solid #ff6b6b;
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
        }

        .input-group.full-width {
            grid-column: 1 / -1;
        }

        .input-group label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-group input:focus {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.15);
        }

        .input-icon {
            position: absolute;
            right: 18px;
            top: 42px;
            color: #000;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .input-icon:hover {
            color: #333;
        }

        .register-btn {
            width: 100%;
            padding: 16px;
            background: #A1BC98;
            border: none;
            border-radius: 50px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(161, 188, 152, 0.4);
            margin-top: 10px;
        }

        .register-btn:hover {
            background: #8fa983;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(161, 188, 152, 0.5);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid #fff;
            transition: all 0.3s;
        }

        .login-link a:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 40px 30px;
            }

            .register-header h2 {
                font-size: 32px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Create Account</h2>
            <p>Join NOVEAUX and start your style journey</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="input-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required />
                </div>

                <div class="input-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required />
                </div>

                <div class="input-group full-width">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="your.email@example.com" required />
                </div>

                <div class="input-group full-width">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" placeholder="Street, City, Province" required />
                </div>

                <div class="input-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" placeholder="+63 XXX XXX XXXX" required />
                </div>

                <div class="input-group">
                    <label for="birthday">Birthday</label>
                    <input type="date" name="birthday" id="birthday" required />
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Create a strong password" required />
                    <span class="input-icon" onclick="togglePassword('password', 'eye-icon-1')">
                        <svg id="eye-icon-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </span>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter your password" required />
                    <span class="input-icon" onclick="togglePassword('confirm_password', 'eye-icon-2')">
                        <svg id="eye-icon-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>
</body>
</html>