<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if ($auth->isLoggedIn()) {
    $auth->redirectBasedOnRole();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } elseif ($auth->adminLogin($email, $password)) {
        $auth->redirectBasedOnRole();
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
    <title>Admin Login - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 2rem; color: var(--secondary-color);">
            Admin Login
        </h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">
                Login
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="../index.php" style="color: var(--primary-color); text-decoration: none;">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html>