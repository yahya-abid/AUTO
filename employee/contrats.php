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

// Handle contract actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_contract'])) {
        $client_id = $_POST['client_id'];
        $car_id = $_POST['car_id'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $depot_garantie = $_POST['depot_garantie'] ?? 0;
        $notes = $functions->sanitize($_POST['notes'] ?? '');
        
        // Get car price
        $car_sql = "SELECT prix_jour FROM car WHERE car_id = ?";
        $stmt = $conn->prepare($car_sql);
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $car = $stmt->get_result()->fetch_assoc();
        
        if (!$car) {
            $error = 'Car not found';
        } else {
            $days = $functions->dateDifference($date_debut, $date_fin);
            $prix_total = $days * $car['prix_jour'];
            
            // Generate contract number
            $contrat_number = 'CTR-' . date('Ymd') . '-' . $functions->generateRandomString(6);
            
            $sql = "INSERT INTO contrat (
                        contrat_number, date_debut, date_fin, depot_garantie,
                        prix_total, prix_jour, status_contrat, client_id,
                        car_id, location_id, processed_by_employee_id, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdddsiiis", 
                $contrat_number, $date_debut, $date_fin, $depot_garantie,
                $prix_total, $car['prix_jour'], $client_id, $car_id,
                $location_id, $employee_id, $notes
            );
            
            if ($stmt->execute()) {
                // Update car availability
                $update_sql = "UPDATE car SET is_available = 0 WHERE car_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $car_id);
                $update_stmt->execute();
                
                $message = 'Contract created successfully. Contract #: ' . $contrat_number;
            } else {
                $error = 'Failed to create contract';
            }
        }
    } elseif (isset($_POST['update_status'])) {
        $contrat_id = $_POST['contrat_id'];
        $status_contrat = $_POST['status_contrat'];
        $date_retour_reel = $_POST['date_retour_reel'] ?? null;
        
        $sql = "UPDATE contrat SET 
                    status_contrat = ?, 
                    date_retour_reel = ?
                WHERE contrat_id = ? AND location_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $status_contrat, $date_retour_reel, $contrat_id, $location_id);
        
        if ($stmt->execute()) {
            // If contract is completed, make car available again
            if ($status_contrat === 'completed') {
                $car_sql = "UPDATE car c 
                           JOIN contrat ct ON c.car_id = ct.car_id 
                           SET c.is_available = 1 
                           WHERE ct.contrat_id = ?";
                $car_stmt = $conn->prepare($car_sql);
                $car_stmt->bind_param("i", $contrat_id);
                $car_stmt->execute();
            }
            
            $message = 'Contract status updated';
        } else {
            $error = 'Failed to update contract status';
        }
    }
}

// Get contracts for this location
$sql = "SELECT c.*, 
               cl.first_name, cl.last_name, cl.telephone_client,
               car.matriculation, CONCAT(car.marque, ' ', car.model) as car_name,
               e.first_name as employee_first, e.last_name as employee_last
        FROM contrat c
        JOIN client cl ON c.client_id = cl.client_id
        JOIN car car ON c.car_id = car.car_id
        LEFT JOIN employee e ON c.processed_by_employee_id = e.employee_id
        WHERE c.location_id = ?
        ORDER BY c.date_creation DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $location_id);
$stmt->execute();
$contracts = $stmt->get_result();

// Get available cars for this location
$cars_sql = "SELECT car_id, matriculation, marque, model, prix_jour 
             FROM car 
             WHERE location_id = ? AND is_available = 1 
             ORDER BY marque, model";
$cars_stmt = $conn->prepare($cars_sql);
$cars_stmt->bind_param("i", $location_id);
$cars_stmt->execute();
$available_cars = $cars_stmt->get_result();

// Get active clients
$clients_sql = "SELECT client_id, first_name, last_name, email, telephone_client 
                FROM client 
                WHERE is_active = 1 
                ORDER BY last_name, first_name";
$clients = $conn->query($clients_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contracts - Employee Panel</title>
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
                <a href="contracts.php" class="active">Contracts</a>
                <a href="payments.php">Payments</a>
                <a href="cars.php">Cars</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>Manage Contracts</h1>
            <p>Create and manage rental contracts</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Create Contract Form -->
        <div class="card">
            <div class="card-header">
                <h2>Create New Contract</h2>
                <button class="btn btn-primary" onclick="toggleCreateForm()">
                    <i class="fas fa-plus"></i> New Contract
                </button>
            </div>
            
            <div id="createContractForm" style="display: none; padding: 1.5rem;">
                <form method="POST" action="" id="contractForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="client_id">Client *</label>
                            <select id="client_id" name="client_id" class="form-control" required>
                                <option value="">Select Client</option>
                                <?php while($client = $clients->fetch_assoc()): ?>
                                    <option value="<?php echo $client['client_id']; ?>">
                                        <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name'] . ' - ' . $client['email']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="car_id">Car *</label>
                            <select id="car_id" name="car_id" class="form-control" required>
                                <option value="">Select Car</option>
                                <?php while($car = $available_cars->fetch_assoc()): ?>
                                    <option value="<?php echo $car['car_id']; ?>" data-price="<?php echo $car['prix_jour']; ?>">
                                        <?php echo htmlspecialchars($car['marque'] . ' ' . $car['model'] . ' (' . $car['matriculation'] . ') - ' . CURRENCY . $car['prix_jour']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_debut">Start Date *</label>
                            <input type="date" id="date_debut" name="date_debut" class="form-control" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_fin">End Date *</label>
                            <input type="date" id="date_fin" name="date_fin" class="form-control" required
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="depot_garantie">Deposit Amount</label>
                            <input type="number" step="0.01" id="depot_garantie" name="depot_garantie" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1.5rem 0;">
                        <h4>Contract Summary</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <strong>Total Days:</strong>
                                <span id="totalDays">0</span>
                            </div>
                            <div>
                                <strong>Daily Rate:</strong>
                                <span id="dailyRate"><?php echo CURRENCY; ?>0.00</span>
                            </div>
                            <div>
                                <strong>Total Amount:</strong>
                                <span id="totalAmount" style="font-weight: bold; color: #3498db;"><?php echo CURRENCY; ?>0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="create_contract" class="btn btn-primary">
                        <i class="fas fa-file-contract"></i> Create Contract
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="toggleCreateForm()">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <!-- Active Contracts -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>All Contracts</h2>
                <div class="search-filter-bar">
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="Search contracts..." id="searchInput">
                    </div>
                    <div class="filter-group">
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <table id="contractsTable">
                    <thead>
                        <tr>
                            <th>Contract #</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($contract = $contracts->fetch_assoc()): 
                            $status_class = '';
                            $status_text = $contract['status_contrat'];
                            if ($status_text == 'confirmed') $status_class = 'status-pending';
                            elseif ($status_text == 'active') $status_class = 'status-active';
                            elseif ($status_text == 'completed') $status_class = 'status-completed';
                            else $status_class = 'status-inactive';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contract['contrat_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?><br>
                                <small><?php echo htmlspecialchars($contract['telephone_client']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($contract['car_name']); ?></td>
                            <td><?php echo $functions->formatDate($contract['date_debut']); ?></td>
                            <td><?php echo $functions->formatDate($contract['date_fin']); ?></td>
                            <td><?php echo CURRENCY . number_format($contract['prix_total'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($status_text); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($contract['employee_first']): ?>
                                    <?php echo htmlspecialchars($contract['employee_first'] . ' ' . $contract['employee_last']); ?>
                                <?php else: ?>
                                    <em>Unknown</em>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <a href="contract-details.php?id=<?php echo $contract['contrat_id']; ?>" 
                                   class="btn-icon btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($contract['status_contrat'] != 'completed' && $contract['status_contrat'] != 'cancelled'): ?>
                                    <button class="btn-icon btn-edit" title="Update Status"
                                            onclick="openStatusModal(<?php echo $contract['contrat_id']; ?>, '<?php echo $contract['status_contrat']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Today's Activities -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>Today's Activities</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; padding: 1.5rem;">
                <?php
                // Today's pickups
                $pickups_sql = "SELECT COUNT(*) as count FROM contrat 
                               WHERE location_id = ? AND date_debut = CURDATE() 
                               AND status_contrat IN ('confirmed', 'active')";
                $stmt = $conn->prepare($pickups_sql);
                $stmt->bind_param("i", $location_id);
                $stmt->execute();
                $pickups = $stmt->get_result()->fetch_assoc();
                
                // Today's returns
                $returns_sql = "SELECT COUNT(*) as count FROM contrat 
                               WHERE location_id = ? AND date_fin = CURDATE() 
                               AND status_contrat IN ('confirmed', 'active')";
                $stmt = $conn->prepare($returns_sql);
                $stmt->bind_param("i", $location_id);
                $stmt->execute();
                $returns = $stmt->get_result()->fetch_assoc();
                
                // Overdue returns
                $overdue_sql = "SELECT COUNT(*) as count FROM contrat 
                               WHERE location_id = ? AND date_fin < CURDATE() 
                               AND status_contrat = 'active'";
                $stmt = $conn->prepare($overdue_sql);
                $stmt->bind_param("i", $location_id);
                $stmt->execute();
                $overdue = $stmt->get_result()->fetch_assoc();
                ?>
                
                <div style="border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
                        <?php echo $pickups['count']; ?>
                    </div>
                    <div>Today's Pickups</div>
                </div>
                
                <div style="border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: #2ecc71;">
                        <?php echo $returns['count']; ?>
                    </div>
                    <div>Today's Returns</div>
                </div>
                
                <div style="border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: bold; color: <?php echo $overdue['count'] > 0 ? '#e74c3c' : '#f39c12'; ?>;">
                        <?php echo $overdue['count']; ?>
                    </div>
                    <div>Overdue Returns</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Contract Status</h3>
                <button class="modal-close" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="statusForm">
                    <input type="hidden" name="contrat_id" id="statusContractId">
                    
                    <div class="form-group">
                        <label for="status_contrat">Status</label>
                        <select id="status_contrat" name="status_contrat" class="form-control">
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_retour_reel">Actual Return Date</label>
                        <input type="date" id="date_retour_reel" name="date_retour_reel" class="form-control">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            Update Status
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleCreateForm() {
            const form = document.getElementById('createContractForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
        
        function openStatusModal(contractId, currentStatus) {
            document.getElementById('statusContractId').value = contractId;
            document.getElementById('status_contrat').value = currentStatus;
            
            // Set today's date for return date if completing contract
            if (currentStatus === 'active') {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('date_retour_reel').value = today;
            }
            
            document.getElementById('statusModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Calculate contract total
        document.getElementById('car_id').addEventListener('change', calculateTotal);
        document.getElementById('date_debut').addEventListener('change', calculateTotal);
        document.getElementById('date_fin').addEventListener('change', calculateTotal);
        
        function calculateTotal() {
            const carSelect = document.getElementById('car_id');
            const startDate = document.getElementById('date_debut').value;
            const endDate = document.getElementById('date_fin').value;
            
            if (carSelect.value && startDate && endDate) {
                const price = parseFloat(carSelect.selectedOptions[0].getAttribute('data-price'));
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                
                document.getElementById('dailyRate').textContent = CURRENCY + price.toFixed(2);
                document.getElementById('totalDays').textContent = days;
                document.getElementById('totalAmount').textContent = CURRENCY + (price * days).toFixed(2);
            }
        }
        
        // Search and filter
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('contractsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const contractNum = cells[0].textContent.toLowerCase();
                const clientName = cells[1].textContent.toLowerCase();
                const carName = cells[2].textContent.toLowerCase();
                const status = cells[6].textContent.trim().toLowerCase();
                
                let show = true;
                
                if (searchTerm && !contractNum.includes(searchTerm) && !clientName.includes(searchTerm) && !carName.includes(searchTerm)) {
                    show = false;
                }
                
                if (statusFilter && status !== statusFilter) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
            }
        }
    </script>
</body>
</html>