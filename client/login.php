<?php
session_start();
require_once '../config/database.php';

// If already logged in as client, redirect to index
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client') {
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill all fields';
    } else {
        try {
            // Query database for client
            $stmt = $pdo->prepare("SELECT client_id, first_name, last_name, email, mot_de_passe FROM client WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($client) {
                // Verify password (adjust based on your hashing method)
                // If passwords are hashed:
                if (password_verify($password, $client['mot_de_passe'])) {
                    // Or if passwords are not hashed (for testing only):
                    // if ($password === $client['mot_de_passe']) {
                    
                    // Set session variables
                    $_SESSION['user_id'] = $client['client_id'];
                    $_SESSION['user_role'] = 'client';
                    $_SESSION['client_id'] = $client['client_id']; // Add this line
                    $_SESSION['user_name'] = $client['first_name'] . ' ' . $client['last_name'];
                    $_SESSION['user_email'] = $client['email'];
                    
                    // Clear any errors
                    unset($_SESSION['error']);
                    
                    // Redirect to index page
                    header('Location: index.php');
                    exit();
                } else {
                    $_SESSION['error'] = 'Invalid email or password';
                }
            } else {
                $_SESSION['error'] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // Reload page to show error
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - CarRental Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="../index.php" class="logo">CarRental Pro</a>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="cars.php">Available Cars</a></li>
                <li><a href="login.php">Client Login</a></li>
                <li><a href="../admin/login.php">Admin Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="form-container" style="margin-top: 50px;">
        <h2 style="text-align: center; color: #1e3c72; margin-bottom: 30px;">Client Login</h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>

            <div class="form-group">
                <button type="submit" name="login" class="btn btn-block">
                    Login
                </button>
            </div>

            <p style="text-align: center; margin-top: 20px; color: #666;">
                Don't have an account? <a href="register.php" style="color: #2196f3; text-decoration: none; font-weight: 600;">Register here</a>
            </p>

            <p style="text-align: center; margin-top: 10px;">
                <a href="forgot-password.php" style="color: #666; text-decoration: none; font-size: 14px;">Forgot Password?</a>
            </p>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 CarRental Pro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>