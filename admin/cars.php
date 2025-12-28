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
    if (isset($_POST['add_car'])) {
        $matriculation = $functions->sanitize($_POST['matriculation']);
        $marque = $functions->sanitize($_POST['marque']);
        $model = $functions->sanitize($_POST['model']);
        $annee = $_POST['annee'];
        $couleur = $functions->sanitize($_POST['couleur']);
        $type_carburant = $_POST['type_carburant'];
        $transmission = $_POST['transmission'];
        $kilometrage = $_POST['kilometrage'];
        $prix_jour = $_POST['prix_jour'];
        $location_id = $_POST['location_id'];
        $nombre_portes = $_POST['nombre_portes'];
        $nombre_places = $_POST['nombre_places'];
        $puissance_fiscale = $_POST['puissance_fiscale'];
        
        // Check if license plate exists
        $check_sql = "SELECT car_id FROM car WHERE matriculation = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $matriculation);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'License plate already exists';
        } else {
            $sql = "INSERT INTO car (
                        matriculation, marque, model, annee, couleur,
                        type_carburant, transmission, kilometrage, prix_jour,
                        location_id, nombre_portes, nombre_places, puissance_fiscale,
                        etat_car, is_available
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'excellent', 1)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssisssidiiii", 
                $matriculation, $marque, $model, $annee, $couleur,
                $type_carburant, $transmission, $kilometrage, $prix_jour,
                $location_id, $nombre_portes, $nombre_places, $puissance_fiscale
            );
            
            if ($stmt->execute()) {
                $car_id = $stmt->insert_id;
                
                // Add features if provided
                if (!empty($_POST['features'])) {
                    foreach ($_POST['features'] as $feature) {
                        if (!empty($feature['type']) && !empty($feature['name'])) {
                            $feature_sql = "INSERT INTO car_feature (car_id, feature_type, feature_name, feature_value) 
                                           VALUES (?, ?, ?, ?)";
                            $feature_stmt = $conn->prepare($feature_sql);
                            $feature_stmt->bind_param("isss", $car_id, $feature['type'], $feature['name'], $feature['value']);
                            $feature_stmt->execute();
                        }
                    }
                }
                
                $message = 'Car added successfully';
            } else {
                $error = 'Failed to add car';
            }
        }
    } elseif (isset($_POST['update_car'])) {
        $car_id = $_POST['car_id'];
        $marque = $functions->sanitize($_POST['marque']);
        $model = $functions->sanitize($_POST['model']);
        $annee = $_POST['annee'];
        $couleur = $functions->sanitize($_POST['couleur']);
        $kilometrage = $_POST['kilometrage'];
        $prix_jour = $_POST['prix_jour'];
        $location_id = $_POST['location_id'];
        $etat_car = $_POST['etat_car'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        $sql = "UPDATE car SET 
                    marque = ?, model = ?, annee = ?, couleur = ?,
                    kilometrage = ?, prix_jour = ?, location_id = ?,
                    etat_car = ?, is_available = ?
                WHERE car_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisiisdii", 
            $marque, $model, $annee, $couleur, $kilometrage,
            $prix_jour, $location_id, $etat_car, $is_available, $car_id
        );
        
        if ($stmt->execute()) {
            $message = 'Car updated successfully';
        } else {
            $error = 'Failed to update car';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $car_id = $_GET['delete'];
    $sql = "UPDATE car SET is_available = 0 WHERE car_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $car_id);
    if ($stmt->execute()) {
        $message = 'Car marked as unavailable';
    }
}

// Get all cars with location info
$sql = "SELECT c.*, l.nom as location_name, l.ville 
        FROM car c 
        JOIN location l ON c.location_id = l.location_id 
        ORDER BY c.is_available DESC, c.marque, c.model";
$cars = $conn->query($sql);

// Get locations for dropdown
$locations_sql = "SELECT location_id, nom, ville FROM location ORDER BY ville";
$locations = $conn->query($locations_sql);

// Get car features types for reference
$feature_types = ['interior', 'exterior', 'safety', 'entertainment', 'comfort'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin Dashboard</title>
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
            <a href="cars.php" class="active"><i class="fas fa-car"></i><span>Cars</span></a>
            <a href="locations.php"><i class="fas fa-map-marker-alt"></i><span>Locations</span></a>
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
            <h1>Manage Cars</h1>
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
                    <i class="fas fa-car"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $total_cars_sql = "SELECT COUNT(*) as total FROM car";
                        $result = $conn->query($total_cars_sql);
                        $total_cars = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $total_cars; ?></h3>
                        <p>Total Cars</p>
                    </div>
                    <div class="card-trend trend-up">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>
            </div>
            
            <div class="admin-card success">
                <div class="card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $available_sql = "SELECT COUNT(*) as total FROM car WHERE is_available = 1";
                        $result = $conn->query($available_sql);
                        $available_cars = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $available_cars; ?></h3>
                        <p>Available Cars</p>
                    </div>
                    <div class="card-trend trend-up">
                        <i class="fas fa-arrow-up"></i> 8%
                    </div>
                </div>
            </div>
            
            <div class="admin-card warning">
                <div class="card-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $maintenance_sql = "SELECT COUNT(*) as total FROM car WHERE etat_car = 'maintenance'";
                        $result = $conn->query($maintenance_sql);
                        $maintenance_cars = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $maintenance_cars; ?></h3>
                        <p>In Maintenance</p>
                    </div>
                    <div class="card-trend trend-down">
                        <i class="fas fa-arrow-down"></i> 3%
                    </div>
                </div>
            </div>
            
            <div class="admin-card info">
                <div class="card-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <?php
                        $locations_sql = "SELECT COUNT(DISTINCT location_id) as total FROM car";
                        $result = $conn->query($locations_sql);
                        $car_locations = $result->fetch_assoc()['total'];
                        ?>
                        <h3><?php echo $car_locations; ?></h3>
                        <p>Active Locations</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Add New Car</h2>
                <button class="btn btn-primary" onclick="toggleAddForm()">
                    <i class="fas fa-plus"></i> Add Car
                </button>
            </div>
            
            <div id="addCarForm" style="display: none; padding: 1.5rem;">
                <form method="POST" action="" id="carForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="matriculation">License Plate *</label>
                            <input type="text" id="matriculation" name="matriculation" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="marque">Brand *</label>
                            <input type="text" id="marque" name="marque" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model *</label>
                            <input type="text" id="model" name="model" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="annee">Year *</label>
                            <input type="number" id="annee" name="annee" class="form-control" 
                                   min="2000" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="couleur">Color</label>
                            <input type="text" id="couleur" name="couleur" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="type_carburant">Fuel Type</label>
                            <select id="type_carburant" name="type_carburant" class="form-control">
                                <option value="essence">Gasoline</option>
                                <option value="diesel">Diesel</option>
                                <option value="electrique">Electric</option>
                                <option value="hybride">Hybrid</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission" class="form-control">
                                <option value="manuelle">Manual</option>
                                <option value="automatique">Automatic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kilometrage">Mileage</label>
                            <input type="number" id="kilometrage" name="kilometrage" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label for="prix_jour">Daily Price *</label>
                            <input type="number" step="0.01" id="prix_jour" name="prix_jour" class="form-control" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="location_id">Location *</label>
                            <select id="location_id" name="location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                <?php while($loc = $locations->fetch_assoc()): ?>
                                    <option value="<?php echo $loc['location_id']; ?>">
                                        <?php echo htmlspecialchars($loc['nom'] . ' - ' . $loc['ville']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nombre_portes">Number of Doors</label>
                            <input type="number" id="nombre_portes" name="nombre_portes" class="form-control" min="2" max="5" value="5">
                        </div>
                        <div class="form-group">
                            <label for="nombre_places">Number of Seats</label>
                            <input type="number" id="nombre_places" name="nombre_places" class="form-control" min="2" max="9" value="5">
                        </div>
                        <div class="form-group">
                            <label for="puissance_fiscale">Tax Power</label>
                            <input type="number" id="puissance_fiscale" name="puissance_fiscale" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div id="featuresContainer">
                        <h3 style="margin-bottom: 1rem;">Features</h3>
                        <div id="featureFields">
                            <div class="feature-row" style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="features[0][type]" class="form-control">
                                        <?php foreach($feature_types as $type): ?>
                                            <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="features[0][name]" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Value</label>
                                    <input type="text" name="features[0][value]" class="form-control">
                                </div>
                                <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addFeature()">
                            <i class="fas fa-plus"></i> Add Feature
                        </button>
                    </div>
                    
                    <button type="submit" name="add_car" class="btn btn-primary" style="margin-top: 1.5rem;">
                        <i class="fas fa-save"></i> Save Car
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="toggleAddForm()" style="margin-top: 1.5rem;">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>All Cars</h2>
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
                        <select class="form-control" id="locationFilter">
                            <option value="">All Locations</option>
                            <?php 
                            $locations->data_seek(0); // Reset pointer
                            while($loc = $locations->fetch_assoc()): ?>
                                <option value="<?php echo $loc['ville']; ?>"><?php echo $loc['ville']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="admin-table">
                <table id="carsTable">
                    <thead>
                        <tr>
                            <th data-sort="id">ID</th>
                            <th data-sort="license">License Plate</th>
                            <th data-sort="brand">Brand/Model</th>
                            <th data-sort="year">Year</th>
                            <th data-sort="location">Location</th>
                            <th data-sort="price">Daily Price</th>
                            <th data-sort="status">Status</th>
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
                            <td><?php echo $car['car_id']; ?></td>
                            <td><?php echo htmlspecialchars($car['matriculation']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($car['marque']); ?></strong><br>
                                <small><?php echo htmlspecialchars($car['model']); ?></small>
                            </td>
                            <td><?php echo $car['annee']; ?></td>
                            <td><?php echo htmlspecialchars($car['ville']); ?></td>
                            <td><?php echo CURRENCY . number_format($car['prix_jour'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="car-details.php?id=<?php echo $car['car_id']; ?>" 
                                   class="btn-icon btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="cars.php?edit=<?php echo $car['car_id']; ?>" 
                                   class="btn-icon btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($car['is_available'] == 1): ?>
                                    <a href="cars.php?delete=<?php echo $car['car_id']; ?>" 
                                       class="btn-icon btn-delete" title="Mark Unavailable"
                                       onclick="return confirm('Mark this car as unavailable?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <button onclick="prevPage()">Previous</button>
                <span id="pageInfo">Page 1 of 1</span>
                <button onclick="nextPage()">Next</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/scripts.js"></script>
    <script>
        let currentPage = 1;
        const rowsPerPage = 10;
        let filteredRows = [];
        
        function toggleAddForm() {
            const form = document.getElementById('addCarForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        function addFeature() {
            const container = document.getElementById('featureFields');
            const index = container.children.length;
            const newRow = document.createElement('div');
            newRow.className = 'feature-row';
            newRow.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 1rem; margin-bottom: 1rem; align-items: end;';
            newRow.innerHTML = `
                <div class="form-group">
                    <label>Type</label>
                    <select name="features[${index}][type]" class="form-control">
                        <?php foreach($feature_types as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="features[${index}][name]" class="form-control">
                </div>
                <div class="form-group">
                    <label>Value</label>
                    <input type="text" name="features[${index}][value]" class="form-control">
                </div>
                <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newRow);
        }
        
        function removeFeature(button) {
            if (document.querySelectorAll('.feature-row').length > 1) {
                button.closest('.feature-row').remove();
            }
        }
        
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('locationFilter').addEventListener('change', filterTable);
        
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const locationFilter = document.getElementById('locationFilter').value.toLowerCase();
            const table = document.getElementById('carsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            filteredRows = [];
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const license = cells[1].textContent.toLowerCase();
                const brand = cells[2].textContent.toLowerCase();
                const location = cells[4].textContent.toLowerCase();
                const status = cells[5].getElementsByClassName('status-badge')[0].textContent.toLowerCase();
                
                let show = true;
                
                // Apply search filter
                if (searchTerm && !license.includes(searchTerm) && !brand.includes(searchTerm)) {
                    show = false;
                }
                
                // Apply status filter
                if (statusFilter) {
                    if (statusFilter === 'available' && status !== 'available') show = false;
                    if (statusFilter === 'unavailable' && status !== 'unavailable') show = false;
                    if (statusFilter === 'maintenance' && status !== 'maintenance') show = false;
                }
                
                // Apply location filter
                if (locationFilter && !location.includes(locationFilter)) {
                    show = false;
                }
                
                if (show) {
                    row.style.display = '';
                    filteredRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            }
            
            currentPage = 1;
            updatePagination();
        }
        
        function updatePagination() {
            const totalRows = filteredRows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
            
            // Hide all filtered rows
            filteredRows.forEach(row => row.style.display = 'none');
            
            // Show rows for current page
            for (let i = startIndex; i < endIndex; i++) {
                if (filteredRows[i]) {
                    filteredRows[i].style.display = '';
                }
            }
            
            // Update page info
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
        }
        
        function nextPage() {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
            }
        }
        
        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            filteredRows = Array.from(document.querySelectorAll('#carsTable tbody tr'));
            updatePagination();
        });
    </script>
</body>
</html>