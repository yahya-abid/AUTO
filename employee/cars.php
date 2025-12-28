<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_EMPLOYEE) && !$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

$location_id = $_SESSION['location_id'];
$employee_id = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle car status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $car_id = $_POST['car_id'];
        $etat_car = $_POST['etat_car'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $kilometrage = $_POST['kilometrage'];
        
        $sql = "UPDATE car SET 
                    etat_car = ?, is_available = ?, kilometrage = ?
                WHERE car_id = ? AND location_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiii", $etat_car, $is_available, $kilometrage, $car_id, $location_id);
        
        if ($stmt->execute()) {
            $message = 'Car status updated successfully';
        } else {
            $error = 'Failed to update car status';
        }
    }
}

// Get cars for this location
$sql = "SELECT c.*, 
               (SELECT COUNT(*) FROM contrat WHERE car_id = c.car_id AND status_contrat = 'active') as active_rentals
        FROM car c 
        WHERE c.location_id = ?
        ORDER BY c.is_available DESC, c.marque, c.model";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$cars = $stmt->get_result();

// Get location details
$location_sql = "SELECT * FROM location WHERE location_id = ?";
$location_stmt = $conn->prepare($location_sql);
$location_stmt->bind_param("i", $location_id);
$location_stmt->execute();
$location = $location_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Employee Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="dashboard.php" class="logo">Employee Panel</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="reservations.php">Reservations</a>
                <a href="contracts.php">Contracts</a>
                <a href="payments.php">Payments</a>
                <a href="cars.php" class="active">Cars</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>Manage Cars</h1>
            <p>Location: <strong><?php echo htmlspecialchars($location['nom'] . ' - ' . $location['ville']); ?></strong></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid" style="margin-bottom: 2rem;">
            <?php
            // Calculate statistics for this location
            $stats_sql = "SELECT 
                            COUNT(*) as total_cars,
                            SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_cars,
                            SUM(CASE WHEN etat_car = 'maintenance' THEN 1 ELSE 0 END) as maintenance_cars,
                            AVG(prix_jour) as avg_price
                          FROM car 
                          WHERE location_id = ?";
            $stmt = $conn->prepare($stats_sql);
            $stmt->bind_param("i", $location_id);
            $stmt->execute();
            $location_stats = $stmt->get_result()->fetch_assoc();
            ?>
            
            <div class="stat-card">
                <h3>Total Cars</h3>
                <div class="number"><?php echo $location_stats['total_cars']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Available</h3>
                <div class="number"><?php echo $location_stats['available_cars']; ?></div>
            </div>
            <div class="stat-card">
                <h3>In Maintenance</h3>
                <div class="number"><?php echo $location_stats['maintenance_cars']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Avg Price</h3>
                <div class="number"><?php echo CURRENCY . number_format($location_stats['avg_price'], 2); ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Cars at This Location</h2>
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="Search cars..." id="searchInput">
                    </div>
                    <div class="filter-group">
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <table id="carsTable">
                    <thead>
                        <tr>
                            <th>License Plate</th>
                            <th>Brand/Model</th>
                            <th>Year</th>
                            <th>Mileage</th>
                            <th>Daily Price</th>
                            <th>Active Rentals</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($car = $cars->fetch_assoc()): 
                            $status_class = '';
                            $status_text = '';
                            if ($car['is_available'] == 0) {
                                $status_class = 'status-inactive';
                                $status_text = 'Unavailable';
                            } elseif ($car['etat_car'] == 'maintenance') {
                                $status_class = 'status-pending';
                                $status_text = 'Maintenance';
                            } else {
                                $status_class = 'status-active';
                                $status_text = 'Available';
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($car['matriculation']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($car['marque']); ?></strong><br>
                                <small><?php echo htmlspecialchars($car['model']); ?></small>
                            </td>
                            <td><?php echo $car['annee']; ?></td>
                            <td><?php echo number_format($car['kilometrage']); ?> km</td>
                            <td><?php echo CURRENCY . number_format($car['prix_jour'], 2); ?></td>
                            <td>
                                <?php if ($car['active_rentals'] > 0): ?>
                                    <span class="badge badge-danger"><?php echo $car['active_rentals']; ?> active</span>
                                <?php else: ?>
                                    <span class="badge badge-success">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn-icon btn-edit" title="Edit Status"
                                        onclick="openEditModal(<?php echo $car['car_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="car-details.php?id=<?php echo $car['car_id']; ?>" 
                                   class="btn-icon btn-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Maintenance Schedule -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>Scheduled Maintenance</h2>
                <a href="maintenance.php" class="btn btn-primary">View All</a>
            </div>
            <?php
            $maintenance_sql = "SELECT m.*, c.matriculation, CONCAT(c.marque, ' ', c.model) as car_name
                                FROM maintenance m
                                JOIN car c ON m.car_id = c.car_id
                                WHERE c.location_id = ? 
                                AND m.status IN ('scheduled', 'in_progress')
                                AND m.date_intervention >= CURDATE()
                                ORDER BY m.date_intervention ASC
                                LIMIT 5";
            $stmt = $conn->prepare($maintenance_sql);
            $stmt->bind_param("i", $location_id);
            $stmt->execute();
            $maintenance = $stmt->get_result();
            
            if ($maintenance->num_rows > 0):
            ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Type</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                            <th>Estimated Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $maintenance->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['car_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['matriculation']); ?></small>
                            </td>
                            <td><?php echo ucfirst($row['type_maintenance']); ?></td>
                            <td><?php echo $functions->formatDate($row['date_intervention']); ?></td>
                            <td>
                                <span class="status-badge <?php 
                                    if ($row['status'] == 'scheduled') echo 'status-pending';
                                    elseif ($row['status'] == 'in_progress') echo 'status-active';
                                    else echo 'status-completed';
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo CURRENCY . number_format($row['cost'], 2); ?></td>
                            <td>
                                <a href="maintenance.php?id=<?php echo $row['maintenance_id']; ?>" 
                                   class="btn btn-primary">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: #666;">
                <p>No scheduled maintenance.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Edit Car Status Modal -->
    <div class="modal" id="editCarModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Car Status</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="editCarForm">
                    <input type="hidden" name="car_id" id="editCarId">
                    
                    <div class="form-group">
                        <label for="editEtatCar">Condition</label>
                        <select id="editEtatCar" name="etat_car" class="form-control">
                            <option value="excellent">Excellent</option>
                            <option value="bon">Good</option>
                            <option value="moyen">Average</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="hors_service">Out of Service</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editKilometrage">Mileage (km)</label>
                        <input type="number" id="editKilometrage" name="kilometrage" class="form-control" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="editIsAvailable">
                            <input type="checkbox" id="editIsAvailable" name="is_available" value="1" checked>
                            Available for Rent
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            Update Status
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(carId) {
            document.getElementById('editCarId').value = carId;
            document.getElementById('editCarModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('editCarModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('carsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const license = cells[0].textContent.toLowerCase();
                const brand = cells[1].textContent.toLowerCase();
                
                if (license.includes(searchTerm) || brand.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const table = document.getElementById('carsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const statusCell = cells[6];
                const statusText = statusCell.textContent.trim().toLowerCase();
                
                if (!status || statusText === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>