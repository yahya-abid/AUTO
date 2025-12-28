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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        
        // Get reservation details
        $sql = "SELECT r.*, c.prix_jour 
                FROM reservation r 
                JOIN car c ON r.car_id = c.car_id 
                WHERE r.reservation_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $reservation = $stmt->get_result()->fetch_assoc();
        
        // Calculate total price
        $days = $functions->dateDifference($reservation['date_debut'], $reservation['date_fin']);
        $total_price = $days * $reservation['prix_jour'];
        
        // Create contract
        $contrat_number = 'CTR-' . date('Ymd') . '-' . $functions->generateRandomString(6);
        
        $sql = "INSERT INTO contrat (
                    contrat_number, date_debut, date_fin, prix_total, prix_jour,
                    status_contrat, client_id, car_id, location_id, processed_by_employee_id
                ) VALUES (?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssddiiii", 
            $contrat_number, $reservation['date_debut'], $reservation['date_fin'],
            $total_price, $reservation['prix_jour'], $reservation['client_id'],
            $reservation['car_id'], $location_id, $employee_id
        );
        
        if ($stmt->execute()) {
            // Update reservation status
            $update_sql = "UPDATE reservation SET status = 'confirmed' WHERE reservation_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $reservation_id);
            $update_stmt->execute();
            
            // Update car availability
            $car_sql = "UPDATE car SET is_available = 0 WHERE car_id = ?";
            $car_stmt = $conn->prepare($car_sql);
            $car_stmt->bind_param("i", $reservation['car_id']);
            $car_stmt->execute();
            
            $message = 'Reservation confirmed and contract created';
        } else {
            $error = 'Failed to create contract';
        }
    }
}

// Get all reservations
$sql = "SELECT r.*, c.first_name, c.last_name, c.telephone_client, 
               car.matriculation, car.marque, car.model,
               l.ville as location_city
        FROM reservation r
        JOIN client c ON r.client_id = c.client_id
        JOIN car car ON r.car_id = car.car_id
        JOIN location l ON car.location_id = l.location_id
        WHERE r.status = 'pending'
        ORDER BY r.date_debut ASC";
$reservations = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Employee Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="dashboard.php" class="logo">Employee Panel</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="reservations.php" class="active">Reservations</a>
                <a href="contracts.php">Contracts</a>
                <a href="payments.php">Payments</a>
                <a href="cars.php">Cars</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>Manage Reservations</h1>
            <p>Confirm pending reservations and create contracts</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Pending Reservations</h2>
            </div>
            <?php if ($reservations->num_rows > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reservation #</th>
                            <th>Client</th>
                            <th>Car</th>
                            <th>Pickup Date</th>
                            <th>Return Date</th>
                            <th>Days</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $reservations->fetch_assoc()): 
                            $days = $functions->dateDifference($row['date_debut'], $row['date_fin']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['reservation_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
                                <small><?php echo htmlspecialchars($row['telephone_client']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['marque'] . ' ' . $row['model']); ?><br>
                                <small><?php echo htmlspecialchars($row['matriculation']); ?></small>
                            </td>
                            <td><?php echo $functions->formatDate($row['date_debut']); ?></td>
                            <td><?php echo $functions->formatDate($row['date_fin']); ?></td>
                            <td><?php echo $days; ?> days</td>
                            <td><?php echo htmlspecialchars($row['location_city']); ?></td>
                            <td class="actions">
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                                    <button type="submit" name="confirm_reservation" class="btn btn-success">
                                        Confirm & Create Contract
                                    </button>
                                </form>
                                <a href="#" class="btn btn-danger" onclick="return confirm('Cancel this reservation?')">
                                    Cancel
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: #666;">
                No pending reservations found.
            </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem;">
            <a href="contracts.php?action=new" class="btn btn-primary">
                Create New Contract Directly
            </a>
        </div>
    </main>
</body>
</html>