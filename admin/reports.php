<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->checkRole(ROLE_ADMIN)) {
    header("Location: ../index.php");
    exit();
}

// Get date range from GET or default to last month
$start_date = $_GET['start_date'] ?? date('Y-m-01', strtotime('-1 month'));
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$location_id = $_GET['location_id'] ?? 'all';

// Get locations for filter
$locations_sql = "SELECT location_id, nom, ville FROM location WHERE is_active = 1 ORDER BY ville";
$locations = $conn->query($locations_sql);

// Get financial report
$financial_sql = "SELECT 
                    DATE_FORMAT(p.date_pay, '%Y-%m') as month,
                    SUM(p.montant) as revenue,
                    COUNT(DISTINCT p.contrat_id) as contracts,
                    COUNT(*) as payments
                  FROM paiement p
                  JOIN contrat c ON p.contrat_id = c.contrat_id
                  WHERE p.date_pay BETWEEN ? AND ?";
                  
if ($location_id !== 'all') {
    $financial_sql .= " AND c.location_id = ?";
    $financial_sql .= " GROUP BY DATE_FORMAT(p.date_pay, '%Y-%m') ORDER BY month";
    $stmt = $conn->prepare($financial_sql);
    $stmt->bind_param("ssi", $start_date, $end_date, $location_id);
} else {
    $financial_sql .= " GROUP BY DATE_FORMAT(p.date_pay, '%Y-%m') ORDER BY month";
    $stmt = $conn->prepare($financial_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
}

$stmt->execute();
$financial_data = $stmt->get_result();

// Get car utilization report
$utilization_sql = "SELECT 
                      c.car_id,
                      c.matriculation,
                      c.marque,
                      c.model,
                      COUNT(ct.contrat_id) as rental_count,
                      SUM(DATEDIFF(ct.date_fin, ct.date_debut)) as total_days,
                      SUM(ct.prix_total) as total_revenue
                    FROM car c
                    LEFT JOIN contrat ct ON c.car_id = ct.car_id 
                      AND ct.date_creation BETWEEN ? AND ?
                      AND ct.status_contrat IN ('completed', 'active')
                    WHERE c.is_available = 1";
                    
if ($location_id !== 'all') {
    $utilization_sql .= " AND c.location_id = ?";
    $utilization_sql .= " GROUP BY c.car_id ORDER BY total_revenue DESC LIMIT 10";
    $stmt = $conn->prepare($utilization_sql);
    $stmt->bind_param("ssi", $start_date, $end_date, $location_id);
} else {
    $utilization_sql .= " GROUP BY c.car_id ORDER BY total_revenue DESC LIMIT 10";
    $stmt = $conn->prepare($utilization_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
}

$stmt->execute();
$utilization_data = $stmt->get_result();

// Get top clients
$clients_sql = "SELECT 
                  cl.client_id,
                  cl.first_name,
                  cl.last_name,
                  cl.email,
                  COUNT(ct.contrat_id) as rental_count,
                  SUM(ct.prix_total) as total_spent,
                  MAX(ct.date_creation) as last_rental
                FROM client cl
                LEFT JOIN contrat ct ON cl.client_id = ct.client_id 
                  AND ct.date_creation BETWEEN ? AND ?
                  AND ct.status_contrat IN ('completed', 'active')
                WHERE cl.is_active = 1";
                
if ($location_id !== 'all') {
    $clients_sql .= " AND ct.location_id = ?";
    $clients_sql .= " GROUP BY cl.client_id ORDER BY total_spent DESC LIMIT 10";
    $stmt = $conn->prepare($clients_sql);
    $stmt->bind_param("ssi", $start_date, $end_date, $location_id);
} else {
    $clients_sql .= " GROUP BY cl.client_id ORDER BY total_spent DESC LIMIT 10";
    $stmt = $conn->prepare($clients_sql);
    $stmt->bind_param("ss", $start_date, $end_date);
}

$stmt->execute();
$clients_data = $stmt->get_result();

// Get overall stats
$stats_sql = "SELECT 
                (SELECT COUNT(*) FROM contrat WHERE date_creation BETWEEN ? AND ?) as total_contracts,
                (SELECT COUNT(DISTINCT client_id) FROM contrat WHERE date_creation BETWEEN ? AND ?) as unique_clients,
                (SELECT SUM(prix_total) FROM contrat WHERE date_creation BETWEEN ? AND ? AND status_contrat IN ('completed', 'active')) as total_revenue,
                (SELECT AVG(DATEDIFF(date_fin, date_debut)) FROM contrat WHERE date_creation BETWEEN ? AND ?) as avg_rental_days";
                
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("ssssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="locations.php"><i class="fas fa-map-marker-alt"></i><span>Locations</span></a>
            <a href="clients.php"><i class="fas fa-user-friends"></i><span>Clients</span></a>
            <a href="contracts.php"><i class="fas fa-file-contract"></i><span>Contracts</span></a>
            <a href="payments.php"><i class="fas fa-credit-card"></i><span>Payments</span></a>
            <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1>Reports & Analytics</h1>
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

        <div class="card">
            <div class="card-header">
                <h2>Report Filters</h2>
            </div>
            <form method="GET" action="" style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" 
                               class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" 
                               class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="form-group">
                        <label for="location_id">Location</label>
                        <select id="location_id" name="location_id" class="form-control">
                            <option value="all">All Locations</option>
                            <?php while($loc = $locations->fetch_assoc()): ?>
                                <option value="<?php echo $loc['location_id']; ?>" 
                                    <?php echo $location_id == $loc['location_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['nom'] . ' - ' . $loc['ville']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-cards">
            <div class="admin-card primary">
                <div class="card-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <h3><?php echo number_format($stats['total_contracts']); ?></h3>
                        <p>Total Contracts</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card success">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <h3><?php echo number_format($stats['unique_clients']); ?></h3>
                        <p>Unique Clients</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card warning">
                <div class="card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <h3><?php echo CURRENCY . number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card info">
                <div class="card-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="card-content">
                    <div class="card-stats">
                        <h3><?php echo number_format($stats['avg_rental_days'] ?? 0, 1); ?></h3>
                        <p>Avg Rental Days</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-card">
                <h3>Monthly Revenue</h3>
                <canvas id="revenueChart" height="250"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Top Performing Cars</h3>
                <canvas id="carsChart" height="250"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Financial Report</h2>
                <button class="btn btn-primary" onclick="exportFinancialReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Revenue</th>
                            <th>Contracts</th>
                            <th>Payments</th>
                            <th>Average Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $financial_data->data_seek(0);
                        $total_revenue = 0;
                        $total_contracts = 0;
                        $total_payments = 0;
                        while($row = $financial_data->fetch_assoc()): 
                            $total_revenue += $row['revenue'];
                            $total_contracts += $row['contracts'];
                            $total_payments += $row['payments'];
                        ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($row['month'] . '-01')); ?></td>
                            <td><?php echo CURRENCY . number_format($row['revenue'], 2); ?></td>
                            <td><?php echo $row['contracts']; ?></td>
                            <td><?php echo $row['payments']; ?></td>
                            <td><?php echo CURRENCY . number_format($row['payments'] > 0 ? $row['revenue'] / $row['payments'] : 0, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr style="font-weight: bold; background-color: #f8f9fa;">
                            <td>Total</td>
                            <td><?php echo CURRENCY . number_format($total_revenue, 2); ?></td>
                            <td><?php echo $total_contracts; ?></td>
                            <td><?php echo $total_payments; ?></td>
                            <td><?php echo CURRENCY . number_format($total_payments > 0 ? $total_revenue / $total_payments : 0, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
            <div class="card">
                <div class="card-header">
                    <h2>Top 10 Cars by Revenue</h2>
                </div>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>License</th>
                                <th>Rentals</th>
                                <th>Total Days</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $utilization_data->data_seek(0);
                            while($row = $utilization_data->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['marque']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['model']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['matriculation']); ?></td>
                                <td><?php echo $row['rental_count']; ?></td>
                                <td><?php echo $row['total_days']; ?></td>
                                <td><?php echo CURRENCY . number_format($row['total_revenue'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Top 10 Clients by Spending</h2>
                </div>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Rentals</th>
                                <th>Total Spent</th>
                                <th>Last Rental</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $clients_data->data_seek(0);
                            while($row = $clients_data->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['rental_count']; ?></td>
                                <td><?php echo CURRENCY . number_format($row['total_spent'], 2); ?></td>
                                <td><?php echo $row['last_rental'] ? date('d/m/Y', strtotime($row['last_rental'])) : 'Never'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        const financialData = [
            <?php 
            $financial_data->data_seek(0);
            $months = [];
            $revenues = [];
            while($row = $financial_data->fetch_assoc()): 
                $months[] = date('M Y', strtotime($row['month'] . '-01'));
                $revenues[] = $row['revenue'];
            ?>
            { month: '<?php echo date('M Y', strtotime($row['month'] . '-01')); ?>', revenue: <?php echo $row['revenue']; ?> },
            <?php endwhile; ?>
        ];

        const carData = [
            <?php 
            $utilization_data->data_seek(0);
            $car_labels = [];
            $car_revenues = [];
            while($row = $utilization_data->fetch_assoc()): 
                $car_labels[] = $row['marque'] . ' ' . $row['model'];
                $car_revenues[] = $row['total_revenue'];
            ?>
            { car: '<?php echo htmlspecialchars($row['marque'] . ' ' . $row['model']); ?>', revenue: <?php echo $row['total_revenue']; ?> },
            <?php endwhile; ?>
        ];

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ' + CURRENCY + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return CURRENCY + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Cars Chart
        const carsCtx = document.getElementById('carsChart').getContext('2d');
        const carsChart = new Chart(carsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($car_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($car_revenues); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ' + CURRENCY + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return CURRENCY + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        function printReport() {
            window.print();
        }

        function exportFinancialReport() {
            let csv = [
                ['Month', 'Revenue', 'Contracts', 'Payments', 'Average Payment']
            ];
            
            financialData.forEach(row => {
                const avgPayment = row.revenue / (row.payments || 1);
                csv.push([
                    row.month,
                    row.revenue,
                    row.contracts || 0,
                    row.payments || 0,
                    avgPayment.toFixed(2)
                ]);
            });
            
            const csvContent = csv.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = 'financial_report_' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>