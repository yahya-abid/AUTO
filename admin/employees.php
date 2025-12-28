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
    if (isset($_POST['add_employee'])) {
        $first_name = $functions->sanitize($_POST['first_name']);
        $last_name = $functions->sanitize($_POST['last_name']);
        $email = $functions->sanitize($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $poste = $functions->sanitize($_POST['poste']);
        $telephone = $functions->sanitize($_POST['telephone']);
        $hire_date = $_POST['hire_date'];
        $location_id = $_POST['location_id'];
        $salary = $_POST['salary'];
        $is_owner = isset($_POST['is_owner']) ? 1 : 0;
        
        // Check if email exists
        $check_sql = "SELECT employee_id FROM employee WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            $sql = "INSERT INTO employee (
                        first_name, last_name, email, poste, hire_date,
                        telephone_employee, location_id, salary, is_owner, mot_de_passe
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssiids", 
                $first_name, $last_name, $email, $poste, $hire_date,
                $telephone, $location_id, $salary, $is_owner, $password
            );
            
            if ($stmt->execute()) {
                $message = 'Employee added successfully';
            } else {
                $error = 'Failed to add employee';
            }
        }
    } elseif (isset($_POST['update_employee'])) {
        $employee_id = $_POST['employee_id'];
        $first_name = $functions->sanitize($_POST['first_name']);
        $last_name = $functions->sanitize($_POST['last_name']);
        $poste = $functions->sanitize($_POST['poste']);
        $telephone = $functions->sanitize($_POST['telephone']);
        $location_id = $_POST['location_id'];
        $salary = $_POST['salary'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_owner = isset($_POST['is_owner']) ? 1 : 0;
        
        $sql = "UPDATE employee SET 
                    first_name = ?, last_name = ?, poste = ?, 
                    telephone_employee = ?, location_id = ?, salary = ?,
                    is_active = ?, is_owner = ?
                WHERE employee_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiiiii", 
            $first_name, $last_name, $poste, $telephone, $location_id,
            $salary, $is_active, $is_owner, $employee_id
        );
        
        if ($stmt->execute()) {
            $message = 'Employee updated successfully';
        } else {
            $error = 'Failed to update employee';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $employee_id = $_GET['delete'];
    $sql = "UPDATE employee SET is_active = 0 WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    if ($stmt->execute()) {
        $message = 'Employee deactivated successfully';
    }
}

// Get all employees
$sql = "SELECT e.*, l.nom as location_name 
        FROM employee e 
        LEFT JOIN location l ON e.location_id = l.location_id 
        ORDER BY e.last_name, e.first_name";
$employees = $conn->query($sql);

// Get locations for dropdown
$locations_sql = "SELECT location_id, nom, ville FROM location ORDER BY ville";
$locations = $conn->query($locations_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-sidebar">
        <div class="logo">CarRental Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="employees.php" class="active">Employees</a>
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
            <h1>Manage Employees</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo substr($_SESSION['user_name'], 0, 1); ?></div>
                <div><?php echo $_SESSION['user_name']; ?></div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Add New Employee</h2>
            </div>
            <form method="POST" action="" style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="poste">Position</label>
                        <input type="text" id="poste" name="poste" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Phone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="hire_date">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="location_id">Location</label>
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
                        <label for="salary">Salary</label>
                        <input type="number" step="0.01" id="salary" name="salary" class="form-control">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 1rem;">
                        <input type="checkbox" id="is_owner" name="is_owner" value="1">
                        <label for="is_owner" style="margin: 0;">Is Administrator</label>
                    </div>
                </div>
                <button type="submit" name="add_employee" class="btn btn-primary" style="margin-top: 1rem;">
                    Add Employee
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>All Employees</h2>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Location</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($emp = $employees->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $emp['employee_id']; ?></td>
                            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                            <td><?php echo htmlspecialchars($emp['poste']); ?></td>
                            <td><?php echo htmlspecialchars($emp['location_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($emp['telephone_employee']); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; 
                                    background-color: <?php echo $emp['is_active'] ? '#d4edda' : '#f8d7da'; ?>;">
                                    <?php echo $emp['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="employees.php?edit=<?php echo $emp['employee_id']; ?>" 
                                   class="btn btn-primary">Edit</a>
                                <?php if ($emp['is_active'] && $emp['employee_id'] != $_SESSION['user_id']): ?>
                                    <a href="employees.php?delete=<?php echo $emp['employee_id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Are you sure?')">Deactivate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>