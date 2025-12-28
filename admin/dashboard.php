<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

$stats = $functions->getDashboardStats();

// Get recent activities
$sql = "SELECT c.*, cl.first_name, cl.last_name, car.matriculation 
        FROM contrat c 
        JOIN client cl ON c.client_id = cl.client_id 
        JOIN car car ON c.car_id = car.car_id 
        ORDER BY c.date_creation DESC LIMIT 5";
$recent_contrats = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-sidebar">
        <div class="logo">CarRental Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="employees.php">Employees</a>
            <a href="cars.php">Cars</a>
            <a href="locations.php">Locations</a>
            <a href="clients.php">Clients</a>
            <a href="contracts.php">Contracts</a>
            <a href="payments.php">Payments</a>
            <a href="reports.php">Reports</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div>
                    <strong><?php echo $_SESSION['user_name']; ?></strong>
                    <div style="font-size: 0.9rem; color: #666;">Administrator</div>
                </div>
            </div>
        </div>

        <div class="admin-cards">
            <div class="admin-card">
                <div class="card-icon">🚗</div>
                <div class="card-number"><?php echo $stats['total_cars']; ?></div>
                <div class="card-text">Available Cars</div>
            </div>
            <div class="admin-card success">
                <div class="card-icon">👥</div>
                <div class="card-number"><?php echo $stats['total_clients']; ?></div>
                <div class="card-text">Total Clients</div>
            </div>
            <div class="admin-card warning">
                <div class="card-icon">👨‍💼</div>
                <div class="card-number"><?php echo $stats['total_employees']; ?></div>
                <div class="card-text">Employees</div>
            </div>
            <div class="admin-card danger">
                <div class="card-icon">📋</div>
                <div class="card-number"><?php echo $stats['active_rentals']; ?></div>
                <div class="card-text">Active Rentals</div>
            </div>
            <div class="admin-card">
                <div class="card-icon">💰</div>
                <div class="card-number"><?php echo CURRENCY . number_format($stats['monthly_revenue'], 2); ?></div>
                <div class="card-text">Monthly Revenue</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Recent Contracts</h2>
                <a href="contracts.php" class="btn btn-primary">View All</a>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Contract #</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_contrats->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['contrat_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['matriculation']); ?></td>
                            <td><?php echo $functions->formatDate($row['date_debut']); ?></td>
                            <td><?php echo $functions->formatDate($row['date_fin']); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; 
                                    background-color: <?php echo $row['status_contrat'] === 'active' ? '#d4edda' : '#f8d7da'; ?>;">
                                    <?php echo ucfirst($row['status_contrat']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="contracts.php?action=view&id=<?php echo $row['contrat_id']; ?>" 
                                   class="btn btn-primary">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Quick Actions</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem;">
                <a href="employees.php?action=add" class="btn btn-primary">Add New Employee</a>
                <a href="cars.php?action=add" class="btn btn-success">Add New Car</a>
                <a href="locations.php?action=add" class="btn btn-warning">Add New Location</a>
                <a href="reports.php" class="btn btn-danger">Generate Reports</a>
            </div>
        </div>
    </div>
</body>
</html>