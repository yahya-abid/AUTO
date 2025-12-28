<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_EMPLOYEE) && !$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

// Get employee-specific stats
$location_id = $_SESSION['location_id'];
$employee_id = $_SESSION['user_id'];

$stats = [];
$sql = "SELECT COUNT(*) as total FROM car WHERE location_id = ? AND is_available = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['available_cars'] = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM contrat WHERE location_id = ? AND status_contrat = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['active_rentals'] = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM reservation WHERE status = 'pending'";
$result = $conn->query($sql);
$stats['pending_reservations'] = $result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="dashboard.php" class="logo">Employee Panel</a>
            <nav class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="reservations.php">Reservations</a>
                <a href="contracts.php">Contracts</a>
                <a href="payments.php">Payments</a>
                <a href="cars.php">Cars</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-header" style="margin: 2rem 0;">
            <h1>Employee Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div>
                    <strong><?php echo $_SESSION['user_name']; ?></strong>
                    <div style="font-size: 0.9rem; color: #666;">
                        <?php echo $_SESSION['location_name']; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Available Cars</h3>
                <div class="number"><?php echo $stats['available_cars']; ?></div>
                <a href="cars.php" class="btn btn-primary" style="margin-top: 1rem;">View Cars</a>
            </div>
            <div class="stat-card">
                <h3>Active Rentals</h3>
                <div class="number"><?php echo $stats['active_rentals']; ?></div>
                <a href="contracts.php" class="btn btn-primary" style="margin-top: 1rem;">View Contracts</a>
            </div>
            <div class="stat-card">
                <h3>Pending Reservations</h3>
                <div class="number"><?php echo $stats['pending_reservations']; ?></div>
                <a href="reservations.php" class="btn btn-primary" style="margin-top: 1rem;">Manage</a>
            </div>
            <div class="stat-card">
                <h3>Location</h3>
                <div style="font-size: 1.5rem; font-weight: bold; margin: 1rem 0;">
                    <?php echo $_SESSION['location_name']; ?>
                </div>
                <a href="#" class="btn btn-success" style="margin-top: 1rem;">Profile Settings</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Quick Actions</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem;">
                <a href="reservations.php?action=new" class="btn btn-primary">Create Reservation</a>
                <a href="contracts.php?action=new" class="btn btn-success">Create Contract</a>
                <a href="payments.php?action=receive" class="btn btn-warning">Receive Payment</a>
                <a href="cars.php?action=add" class="btn btn-danger">Add Car</a>
            </div>
        </div>

        <?php
        // Get today's pickups
        $sql = "SELECT c.*, cl.first_name, cl.last_name, cl.telephone_client, 
                       car.matriculation, car.marque, car.model
                FROM contrat c
                JOIN client cl ON c.client_id = cl.client_id
                JOIN car car ON c.car_id = car.car_id
                WHERE c.location_id = ? 
                AND c.date_debut = CURDATE() 
                AND c.status_contrat IN ('confirmed', 'active')
                ORDER BY c.date_creation DESC
                LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $location_id);
        $stmt->execute();
        $today_pickups = $stmt->get_result();
        
        if ($today_pickups->num_rows > 0):
        ?>
        <div class="card">
            <div class="card-header">
                <h2>Today's Pickups</h2>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Contract #</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Pickup Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $today_pickups->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['contrat_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
                                <small><?php echo htmlspecialchars($row['telephone_client']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['marque'] . ' ' . $row['model']); ?><br>
                                <small><?php echo htmlspecialchars($row['matriculation']); ?></small>
                            </td>
                            <td>Today</td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; 
                                    background-color: #fff3cd; color: #856404;">
                                    Ready for Pickup
                                </span>
                            </td>
                            <td class="actions">
                                <a href="contracts.php?action=process&id=<?php echo $row['contrat_id']; ?>" 
                                   class="btn btn-success">Process</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>