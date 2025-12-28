<?php
session_start();

// Check if user is logged in as client
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - CarRental Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="../index.php" class="logo">CarRental Pro</a>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="cars.php">Available Cars</a></li>
                <li><span style="color: #2196f3;">Welcome, <?php echo $_SESSION['user_name']; ?>!</span></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome to Your Dashboard</h1>
        <p>Choose an option from the menu above</p>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 CarRental Pro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>