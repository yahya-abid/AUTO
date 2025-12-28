<?php
// Debug: Check what path is being used
echo "<!-- DEBUG: CSS should be at: /car-rental-website/assets/css/style.css -->\n";
echo "<!-- DEBUG: Current URL: " . $_SERVER['REQUEST_URI'] . " -->\n";
echo "<!-- DEBUG: Project root: " . $_SERVER['DOCUMENT_ROOT'] . " -->\n";

if (!isset($no_header)): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Car Rental System</title>
    
    <!-- Debug the actual CSS link -->
    <link rel="stylesheet" href="/car-rental-website/assets/css/style.css" id="main-css">
    
    <?php if (isset($admin_page)): ?>
    <link rel="stylesheet" href="/car-rental-website/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- TEMPORARY: Add inline styles to confirm if CSS works -->
    <style>
    body { background-color: lightblue !important; }
    h1 { color: red !important; }
    </style>
</head>
<body>
    <?php if (!isset($no_nav)): ?>
    <header>
        <div class="container header-content">
            <a href="/car-rental-website/index.php" class="logo">CarRental Pro</a>
            <nav class="nav-links">
                <a href="/car-rental-website/client/index.php">Dashboard</a>
                <a href="/car-rental-website/client/cars.php">Available Cars</a>
                <a href="/car-rental-website/client/reservations.php">My Reservations</a>
                <a href="/car-rental-website/client/profile.php">My Profile</a>
                <a href="/car-rental-website/logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <?php endif; ?>
<?php endif; ?>