<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_location'])) {
        $nom = $functions->sanitize($_POST['nom']);
        $ville = $functions->sanitize($_POST['ville']);
        $country = $functions->sanitize($_POST['country']);
        $owner_name = $functions->sanitize($_POST['owner_name']);
        $capital = $_POST['capital'] ?? 0;
        $phone = $functions->sanitize($_POST['phone']);
        $email = $functions->sanitize($_POST['email']);
        $address = $functions->sanitize($_POST['address']);
        
        // Check if location already exists
        $check_sql = "SELECT location_id FROM location WHERE ville = ? AND nom = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $ville, $nom);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Location already exists in this city';
        } else {
            $sql = "INSERT INTO location (
                        nom, ville, country, owner_name, capital,
                        phone, email, address, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssdsss", 
                $nom, $ville, $country, $owner_name, $capital,
                $phone, $email, $address
            );
            
            if ($stmt->execute()) {
                $message = 'Location added successfully';
            } else {
                $error = 'Failed to add location';
            }
        }
    } elseif (isset($_POST['update_location'])) {
        $location_id = $_POST['location_id'];
        $nom = $functions->sanitize($_POST['nom']);
        $ville = $functions->sanitize($_POST['ville']);
        $country = $functions->sanitize($_POST['country']);
        $owner_name = $functions->sanitize($_POST['owner_name']);
        $capital = $_POST['capital'] ?? 0;
        $phone = $functions->sanitize($_POST['phone']);
        $email = $functions->sanitize($_POST['email']);
        $address = $functions->sanitize($_POST['address']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE location SET 
                    nom = ?, ville = ?, country = ?, owner_name = ?,
                    capital = ?, phone = ?, email = ?, address = ?,
                    is_active = ?
                WHERE location_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsssii", 
            $nom, $ville, $country, $owner_name, $capital,
            $phone, $email, $address, $is_active, $location_id
        );
        
        if ($stmt->execute()) {
            $message = 'Location updated successfully';
        } else {
            $error = 'Failed to update location';
        }
    }
}

// Handle delete (soft delete)
if (isset($_GET['delete'])) {
    $location_id = $_GET['delete'];
    $sql = "UPDATE location SET is_active = 0 WHERE location_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $location_id);
    if ($stmt->execute()) {
        $message = 'Location deactivated';
    }
}

// Get all locations
$sql = "SELECT l.*, 
               (SELECT COUNT(*) FROM car WHERE location_id = l.location_id) as car_count,
               (SELECT COUNT(*) FROM employee WHERE location_id = l.location_id AND is_active = 1) as employee_count
        FROM location l 
        ORDER BY l.is_active DESC, l.ville, l.nom";
$locations = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-sidebar">
        <div class="logo">
            <i class="fas fa-car"></i>
            <span>CarRental Admin</span>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="employees.php"><i class="fas fa-users"></i><span>Employees</span></a>
            <a href="cars.php"><i class="fas fa-car"></i><span>Cars</span></a>
            <a href="locations.php" class="active"><i class="fas fa-map-marker-alt"></i><span>Locations</span></a>
            <a href="clients.php"><i class="fas fa-user-friends"></i><span>Clients</span></a>
            <a href="contracts.php"><i class="fas fa-file-contract"></i><span>Contracts</span></a>
            <a href="payments.php"><i class="fas fa-credit-card"></i><span>Payments</span></a>
            <a href="reports.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1>Manage Locations</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo $_SESSION['user_name']; ?></h3>
                    <p>Administrator</p>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="admin-cards">
            <div class="admin-card primary">
                <div class="card-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $total_locations_sql = "SELECT COUNT(*) as total FROM location WHERE is_active = 1";
                        $result = $conn->query($total_locations_sql);
                        $total_locations = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $total_locations; ?></h3>
                        <p>Active Locations</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card success">
                <div class="card-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $total_cars_sql = "SELECT COUNT(*) as total FROM car WHERE is_available = 1";
                        $result = $conn->query($total_cars_sql);
                        $total_cars = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $total_cars; ?></h3>
                        <p>Available Cars</p>
                    </div>
                    <div class="card-trend trend-up">
                        <i class="fas fa-arrow-up"></i> 5%
                    </div>
                </div>
            </div>
            
            <div class="admin-card warning">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $total_employees_sql = "SELECT COUNT(*) as total FROM employee WHERE is_active = 1";
                        $result = $conn->query($total_employees_sql);
                        $total_employees = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $total_employees; ?></h3>
                        <p>Active Employees</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card info">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $avg_cars_sql = "SELECT AVG(car_count) as avg FROM (
                            SELECT COUNT(*) as car_count FROM car GROUP BY location_id
                        ) as counts";
                        $result = $conn->query($avg_cars_sql);
                        $avg_cars = round($result->fetch_assoc()['avg'] ?? 0);
                        ?>
                        <h3><?php echo $avg_cars; ?></h3>
                        <p>Avg Cars/Location</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Add New Location</h2>
                <button class="btn btn-primary" onclick="toggleAddForm()">
                    <i class="fas fa-plus"></i> Add Location
                </button>
            </div>
            
            <div id="addLocationForm" style="display: none; padding: 1.5rem;">
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="nom">Location Name *</label>
                            <input type="text" id="nom" name="nom" class="form-control" required>
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
                            <label for="owner_name">Manager Name</label>
                            <input type="text" id="owner_name" name="owner_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="capital">Capital</label>
                            <input type="number" step="0.01" id="capital" name="capital" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_location" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Location
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="toggleAddForm()">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>All Locations</h2>
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="Search locations..." id="searchInput">
                    </div>
                    <div class="filter-group">
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button class="btn btn-secondary" onclick="exportLocations()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="admin-table">
                <table id="locationsTable">
                    <thead>
                        <tr>
                            <th data-sort="id">ID</th>
                            <th data-sort="name">Location Name</th>
                            <th data-sort="city">City/Country</th>
                            <th data-sort="manager">Manager</th>
                            <th data-sort="cars">Cars</th>
                            <th data-sort="employees">Employees</th>
                            <th data-sort="capital">Capital</th>
                            <th data-sort="status">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($loc = $locations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $loc['location_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($loc['nom']); ?></strong><br>
                                <small><?php echo htmlspecialchars($loc['phone']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($loc['ville']); ?><br>
                                <small><?php echo htmlspecialchars($loc['country']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($loc['owner_name']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $loc['car_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge badge-warning"><?php echo $loc['employee_count']; ?></span>
                            </td>
                            <td><?php echo CURRENCY . number_format($loc['capital'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $loc['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $loc['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="location-details.php?id=<?php echo $loc['location_id']; ?>" 
                                   class="btn-icon btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="locations.php?edit=<?php echo $loc['location_id']; ?>" 
                                   class="btn-icon btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($loc['is_active'] == 1 && $loc['car_count'] == 0): ?>
                                    <a href="locations.php?delete=<?php echo $loc['location_id']; ?>" 
                                       class="btn-icon btn-delete" title="Deactivate"
                                       onclick="return confirm('Deactivate this location?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if (isset($_GET['edit'])): 
            $edit_id = $_GET['edit'];
            $edit_sql = "SELECT * FROM location WHERE location_id = ?";
            $edit_stmt = $conn->prepare($edit_sql);
            $edit_stmt->bind_param("i", $edit_id);
            $edit_stmt->execute();
            $location_data = $edit_stmt->get_result()->fetch_assoc();
        ?>
        <div class="modal" id="editModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Location</h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="location_id" value="<?php echo $location_data['location_id']; ?>">
                        
                        <div class="form-group">
                            <label for="edit_nom">Location Name *</label>
                            <input type="text" id="edit_nom" name="nom" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['nom']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_ville">City *</label>
                            <input type="text" id="edit_ville" name="ville" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['ville']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_country">Country *</label>
                            <input type="text" id="edit_country" name="country" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['country']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_owner_name">Manager Name</label>
                            <input type="text" id="edit_owner_name" name="owner_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['owner_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_capital">Capital</label>
                            <input type="number" step="0.01" id="edit_capital" name="capital" class="form-control" 
                                   value="<?php echo $location_data['capital']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_phone">Phone *</label>
                            <input type="tel" id="edit_phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_email">Email *</label>
                            <input type="email" id="edit_email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($location_data['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <textarea id="edit_address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($location_data['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_is_active">Status</label>
                            <select id="edit_is_active" name="is_active" class="form-control">
                                <option value="1" <?php echo $location_data['is_active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$location_data['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" name="update_location" class="btn btn-primary">
                                Update Location
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
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('editModal').style.display = 'flex';
            });
        </script>
        <?php endif; ?>
    </div>

    <script src="../assets/js/scripts.js"></script>
    <script>
        function toggleAddForm() {
            const form = document.getElementById('addLocationForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        function closeModal() {
            window.location.href = 'locations.php';
        }
        
        function exportLocations() {
            const table = document.getElementById('locationsTable');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const rowData = [];
                const cells = row.querySelectorAll('th, td');
                
                cells.forEach(cell => {
                    if (!cell.closest('.action-buttons')) {
                        rowData.push(`"${cell.textContent.trim().replace(/"/g, '""')}"`);
                    }
                });
                
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = 'locations_export_' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('locationsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const locationName = cells[1].textContent.toLowerCase();
                const city = cells[2].textContent.toLowerCase();
                const manager = cells[3].textContent.toLowerCase();
                
                if (locationName.includes(searchTerm) || city.includes(searchTerm) || manager.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const table = document.getElementById('locationsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const statusCell = cells[7];
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