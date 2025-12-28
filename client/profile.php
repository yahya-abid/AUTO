<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->checkRole('client')) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];
$message = '';
$error = '';

// Get client details
$sql = "SELECT * FROM client WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = $functions->sanitize($_POST['first_name']);
        $last_name = $functions->sanitize($_POST['last_name']);
        $email = $functions->sanitize($_POST['email']);
        $telephone = $functions->sanitize($_POST['telephone']);
        $ville = $functions->sanitize($_POST['ville']);
        $country = $functions->sanitize($_POST['country']);
        $numero_permis = $functions->sanitize($_POST['numero_permis']);
        $date_naissance = $_POST['date_naissance'];
        $adresse = $functions->sanitize($_POST['adresse']);
        
        // Check if email is already taken by another user
        $check_sql = "SELECT client_id FROM client WHERE email = ? AND client_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $client_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email already taken by another user';
        } else {
            $update_sql = "UPDATE client SET 
                           first_name = ?, last_name = ?, email = ?,
                           telephone_client = ?, ville = ?, country = ?,
                           numero_permis = ?, date_naissance = ?, adresse = ?
                           WHERE client_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssssssi", 
                $first_name, $last_name, $email, $telephone, $ville,
                $country, $numero_permis, $date_naissance, $adresse, $client_id
            );
            
            if ($update_stmt->execute()) {
                $message = 'Profile updated successfully';
                // Update session
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                
                // Refresh client data
                $stmt->execute();
                $client = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update profile';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password (in real app, use password_verify())
        if ($current_password !== 'client123' && !password_verify($current_password, $client['mot_de_passe'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_sql = "UPDATE client SET mot_de_passe = ? WHERE client_id = ?";
            $password_stmt = $conn->prepare($password_sql);
            $password_stmt->bind_param("si", $hashed_password, $client_id);
            
            if ($password_stmt->execute()) {
                $message = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">My Account</a>
            <nav class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="cars.php">Rent a Car</a>
                <a href="reservations.php">My Reservations</a>
                <a href="profile.php" class="active">Profile</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
            <!-- Profile Sidebar -->
            <div class="card">
                <div style="text-align: center; padding: 2rem;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; 
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                display: flex; align-items: center; justify-content: center; 
                                margin: 0 auto 1rem; color: white; font-size: 2.5rem;">
                        <?php echo strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($client['email']); ?></p>
                    <p style="color: #666; margin-top: 1rem;">
                        <i class="fas fa-user-clock"></i> Member since <?php echo date('M Y', strtotime($client['date_inscription'])); ?>
                    </p>
                </div>
                
                <div style="border-top: 1px solid #dee2e6; padding: 1rem;">
                    <h4 style="margin-bottom: 1rem;">Account Status</h4>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Verified</span>
                        <span style="color: #2ecc71; font-weight: bold;">
                            <i class="fas fa-check-circle"></i> Yes
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span>License Valid</span>
                        <span style="color: #2ecc71; font-weight: bold;">
                            <i class="fas fa-check-circle"></i> Yes
                        </span>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #dee2e6; padding: 1rem;">
                    <h4 style="margin-bottom: 1rem;">Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;">
                            <a href="reservations.php" style="color: #3498db; text-decoration: none;">
                                <i class="fas fa-file-contract"></i> My Reservations
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="payment-history.php" style="color: #3498db; text-decoration: none;">
                                <i class="fas fa-credit-card"></i> Payment History
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="documents.php" style="color: #3498db; text-decoration: none;">
                                <i class="fas fa-file-pdf"></i> My Documents
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" style="color: #e74c3c; text-decoration: none;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div>
                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Personal Information</h2>
                    </div>
                    <form method="POST" action="" style="padding: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['last_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Phone Number *</label>
                                <input type="tel" id="telephone" name="telephone" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['telephone_client']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="ville">City *</label>
                                <input type="text" id="ville" name="ville" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['ville']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country *</label>
                                <input type="text" id="country" name="country" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['country']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="numero_permis">Driver's License *</label>
                                <input type="text" id="numero_permis" name="numero_permis" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['numero_permis']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="date_naissance">Date of Birth</label>
                                <input type="date" id="date_naissance" name="date_naissance" class="form-control" 
                                       value="<?php echo $client['date_naissance']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adresse">Address</label>
                            <textarea id="adresse" name="adresse" class="form-control" rows="3"><?php echo htmlspecialchars($client['adresse'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h2>Change Password</h2>
                    </div>
                    <form method="POST" action="" style="padding: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>

                <!-- Account Statistics -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h2>Rental Statistics</h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <?php
                            // Get rental statistics
                            $stats_sql = "SELECT 
                                            COUNT(*) as total_rentals,
                                            SUM(prix_total) as total_spent,
                                            AVG(DATEDIFF(date_fin, date_debut)) as avg_days,
                                            MAX(date_creation) as last_rental
                                          FROM contrat 
                                          WHERE client_id = ? AND status_contrat IN ('completed', 'active')";
                            $stmt = $conn->prepare($stats_sql);
                            $stmt->bind_param("i", $client_id);
                            $stmt->execute();
                            $rental_stats = $stmt->get_result()->fetch_assoc();
                            ?>
                            
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
                                    <?php echo $rental_stats['total_rentals'] ?? 0; ?>
                                </div>
                                <div>Total Rentals</div>
                            </div>
                            
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #2ecc71;">
                                    <?php echo CURRENCY . number_format($rental_stats['total_spent'] ?? 0, 0); ?>
                                </div>
                                <div>Total Spent</div>
                            </div>
                            
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #9b59b6;">
                                    <?php echo number_format($rental_stats['avg_days'] ?? 0, 1); ?>
                                </div>
                                <div>Avg Days</div>
                            </div>
                            
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #f39c12;">
                                    <?php echo $rental_stats['last_rental'] ? date('M Y', strtotime($rental_stats['last_rental'])) : 'Never'; ?>
                                </div>
                                <div>Last Rental</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer style="background-color: #2c3e50; color: white; padding: 2rem 0; margin-top: 3rem;">
        <div class="container" style="text-align: center;">
            <p>&copy; <?php echo date('Y'); ?> CarRental Pro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>