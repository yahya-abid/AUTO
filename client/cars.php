<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_once __DIR__ . '/../config/constants.php';




$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query
$sql = "SELECT c.*, l.ville, l.nom as location_name 
        FROM car c 
        JOIN location l ON c.location_id = l.location_id 
        WHERE c.is_available = 1 
        AND c.etat_car IN ('excellent', 'bon', 'moyen')";

$params = [];
$types = '';

if ($search) {
    $sql .= " AND (c.marque LIKE ? OR c.model LIKE ? OR c.matriculation LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

if ($location) {
    $sql .= " AND l.ville = ?";
    $params[] = $location;
    $types .= 's';
}

if ($min_price) {
    $sql .= " AND c.prix_jour >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if ($max_price) {
    $sql .= " AND c.prix_jour <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

$sql .= " ORDER BY c.prix_jour ASC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cars = $stmt->get_result();

// Get unique cities for filter
$cities_sql = "SELECT DISTINCT ville FROM location ORDER BY ville";
$cities = $conn->query($cities_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="../index.php" class="logo">CarRental Pro</a>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="cars.php" class="active">Available Cars</a>
                <?php if ($auth->isLoggedIn() && $auth->checkRole('client')): ?>
                    <a href="profile.php">My Profile</a>
                    <a href="reservations.php">My Reservations</a>
                    <a href="../logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 2rem 0;">
            <h1>Available Cars</h1>
            <p>Browse our selection of available vehicles</p>
        </div>

        <!-- Filter Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem;">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Brand, model, or license" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <select id="location" name="location" class="form-control">
                        <option value="">All Locations</option>
                        <?php while($city = $cities->fetch_assoc()): ?>
                            <option value="<?php echo $city['ville']; ?>" 
                                <?php echo $location === $city['ville'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['ville']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_price">Min Price (per day)</label>
                    <input type="number" id="min_price" name="min_price" class="form-control" 
                           placeholder="0" value="<?php echo htmlspecialchars($min_price); ?>" step="0.01">
                </div>
                <div class="form-group">
                    <label for="max_price">Max Price (per day)</label>
                    <input type="number" id="max_price" name="max_price" class="form-control" 
                           placeholder="500" value="<?php echo htmlspecialchars($max_price); ?>" step="0.01">
                </div>
                <div style="display: flex; align-items: flex-end; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="cars.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Cars Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <?php while($car = $cars->fetch_assoc()): ?>
            <div class="card">
                <div style="background: #f0f0f0; height: 200px; border-radius: 8px; margin-bottom: 1rem;
                            display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                    🚗
                </div>
                <h3><?php echo htmlspecialchars($car['marque'] . ' ' . $car['model']); ?></h3>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($car['annee']); ?></p>
                <p><strong>License:</strong> <?php echo htmlspecialchars($car['matriculation']); ?></p>
                <p><strong>Fuel:</strong> <?php echo htmlspecialchars(ucfirst($car['type_carburant'])); ?></p>
                <p><strong>Transmission:</strong> <?php echo htmlspecialchars(ucfirst($car['transmission'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($car['ville']); ?></p>
                <p><strong>Status:</strong> 
                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; 
                        background-color: #d4edda; color: #155724;">
                        Available
                    </span>
                </p>
                <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color); 
                            margin: 1rem 0;">
                    <?php echo CURRENCY . number_format($car['prix_jour'], 2); ?> <small>/day</small>
                </div>
                
                <?php if ($auth->isLoggedIn() && $auth->checkRole('client')): ?>
                    <a href="reservation.php?car_id=<?php echo $car['car_id']; ?>" 
                       class="btn btn-primary" style="width: 100%;">
                        Rent This Car
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=cars.php" 
                       class="btn btn-primary" style="width: 100%;">
                        Login to Rent
                    </a>
                <?php endif; ?>
                
                <div style="margin-top: 1rem;">
                    <a href="car-details.php?id=<?php echo $car['car_id']; ?>" 
                       class="btn btn-secondary" style="width: 100%;">
                        View Details
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($cars->num_rows === 0): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <h3>No cars found</h3>
            <p>Try adjusting your filters or check back later.</p>
            <a href="cars.php" class="btn btn-primary">Clear Filters</a>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>