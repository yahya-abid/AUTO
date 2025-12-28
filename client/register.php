<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($auth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $functions->sanitize($_POST['first_name']);
    $last_name = $functions->sanitize($_POST['last_name']);
    $email = $functions->sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telephone = $functions->sanitize($_POST['telephone']);
    $ville = $functions->sanitize($_POST['ville']);
    $country = $functions->sanitize($_POST['country']);
    $numero_permis = $functions->sanitize($_POST['numero_permis']);
    $date_naissance = $_POST['date_naissance'];
    
    // Check if email exists
    $check_sql = "SELECT client_id FROM client WHERE email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $error = 'Email already registered';
    } else {
        $sql = "INSERT INTO client (
                    first_name, last_name, email, mot_de_passe,
                    telephone_client, ville, country, numero_permis,
                    date_naissance, is_active, is_verified
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", 
            $first_name, $last_name, $email, $password,
            $telephone, $ville, $country, $numero_permis, $date_naissance
        );
        
        if ($stmt->execute()) {
            $success = 'Registration successful! You can now login.';
            header("refresh:2;url=login.php");
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="../index.php" class="logo">CarRental Pro</a>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="cars.php">Available Cars</a>
                <a href="login.php">Login</a>
                <a href="register.php" class="active">Register</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 2rem; color: var(--secondary-color);">
                Create Account
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Phone Number *</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">City *</label>
                        <input type="text" id="ville" name="ville" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country *</label>
                        <input type="text" id="country" name="country" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="numero_permis">Driver's License Number *</label>
                        <input type="text" id="numero_permis" name="numero_permis" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date of Birth *</label>
                        <input type="date" id="date_naissance" name="date_naissance" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; margin-top: 1rem;">
                    Register
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Already have an account? <a href="login.php" style="color: var(--primary-color);">Login here</a></p>
                <p><a href="../index.php" style="color: var(--primary-color); text-decoration: none;">
                    ← Back to Home
                </a></p>
            </div>
        </div>
    </main>
</body>
</html>