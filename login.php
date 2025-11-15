<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stored_password = $user['password'];

            if (password_verify($password, $stored_password)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            } elseif ($password === $stored_password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            } else {
                $error_message = 'Invalid password.';
            }
        } else {
            $error_message = 'No user found with that email.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>NOVEAUX - Login</title>
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
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .login-header h2 {
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            margin-bottom: 40px;
            font-weight: 400;
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

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 16px 50px 16px 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            font-size: 15px;
            outline: none;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .input-group input:focus {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.15);
        }

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 20px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .input-icon:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .remember-me {
            display: none;
        }

        .login-btn {
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
            margin-top: 30px;
        }

        .login-btn:hover {
            background: #8fa983;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(161, 188, 152, 0.5);
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        .signup-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid #fff;
            transition: all 0.3s;
        }

        .signup-link a:hover {
            opacity: 0.8;
        }

        .footer-credit {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .login-header h2 {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Noveaux Login</h2>
            <p>Welcome please login to your account</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="User Name" required />
                <span class="input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
            </div>

            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Password" required />
                <span class="input-icon" onclick="togglePassword()">
                    <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" />
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="register.php">Signup</a>
        </div>

        <div class="footer-credit">
            Created by <strong>NOVEAUX</strong>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
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