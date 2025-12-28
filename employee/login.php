<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if ($auth->isLoggedIn()) {
    $auth->redirectBasedOnRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } elseif ($auth->adminLogin($email, $password)) {
        // Redirect based on role
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="form-container" style="max-width: 400px; margin-top: 5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 80px; height: 80px; border-radius: 50%; 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        display: flex; align-items: center; justify-content: center; 
                        margin: 0 auto 1rem; color: white; font-size: 2rem;">
                <i class="fas fa-user-tie"></i>
            </div>
            <h2 style="color: #2c3e50;">Employee Login</h2>
            <p style="color: #666;">Access your employee dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div style="position: relative;">
                    <input type="email" id="email" name="email" class="form-control" required 
                           style="padding-left: 2.5rem;">
                    <i class="fas fa-envelope" style="position: absolute; left: 1rem; top: 50%; 
                       transform: translateY(-50%); color: #666;"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" required
                           style="padding-left: 2.5rem;">
                    <i class="fas fa-lock" style="position: absolute; left: 1rem; top: 50%; 
                       transform: translateY(-50%); color: #666;"></i>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="remember" style="margin: 0;">
                    <span>Remember me</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #dee2e6;">
            <p style="margin-bottom: 0.5rem;">
                <a href="../index.php" style="color: #3498db; text-decoration: none;">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </p>
            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                Need help? Contact your administrator
            </p>
        </div>
    </div>
</body>
</html>