<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Load the hardcoded admin configuration file
$admin_config = require_once __DIR__ . '/../config/admin_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Validate against the hardcoded config file array
        if ($email === $admin_config['email'] && password_verify($password, $admin_config['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = 1; // Dummy identifier for session validation
            $_SESSION['admin_name'] = $admin_config['name'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please enter your email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Admin Login</title>
    <!-- Absolute stylesheet path matching your project directory -->
    <link rel="stylesheet" href="../assets/css/login_style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-container">
    <div class="left-panel">
        <div class="illustration">
            <div class="plate"></div>
            <div class="fork"></div>
            <div class="knife"></div>
            <div class="burger">🍔</div>
            <h2>Restaurant QR Menu</h2>
            <p>
                Manage your menu, categories, prices,
                availability and customer experience
                from one beautiful dashboard.
            </p>
        </div>
    </div>

    <div class="right-panel">
        <div class="login-box">
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to continue</p>

            <?php if($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
