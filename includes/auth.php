<?php
// includes/auth.php - UPDATED WITH CORRECT TABLE NAMES
require_once __DIR__ . '/../config/constants.php';

class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->initSession();
    }
    
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('CAR_RENTAL_SESSION');
            session_start();
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }
    
    public function checkRole($requiredRole) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $_SESSION['role'] === $requiredRole;
    }
    
    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function clientLogin($email, $password) {
        // FIXED: Using correct table name 'client' and column names
        $stmt = $this->conn->prepare("SELECT client_id, email, mot_de_passe, first_name, last_name FROM client WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Note: Your database uses 'mot_de_passe' for password
            // For testing: plain password comparison
            if ($password === $user['mot_de_passe']) {
                $_SESSION['user_id'] = $user['client_id'];  // client_id, not id
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = ROLE_CLIENT;
                $_SESSION['logged_in'] = true;
                session_regenerate_id(true);
                return true;
            }
        }
        return false;
    }
    
    public function adminLogin($username, $password) {
        // FIXED: Your schema doesn't have admins table, using employees instead
        $stmt = $this->conn->prepare("SELECT employee_id, email, mot_de_passe, first_name, last_name, poste FROM employee WHERE email = ? AND poste LIKE '%admin%'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($password === $user['mot_de_passe']) {
                $_SESSION['user_id'] = $user['employee_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = ROLE_ADMIN;
                $_SESSION['logged_in'] = true;
                session_regenerate_id(true);
                return true;
            }
        }
        return false;
    }
    
    public function employeeLogin($email, $password) {
        $stmt = $this->conn->prepare("SELECT employee_id, email, mot_de_passe, first_name, last_name, poste FROM employee WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($password === $user['mot_de_passe']) {
                $_SESSION['user_id'] = $user['employee_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = ROLE_EMPLOYEE;
                $_SESSION['poste'] = $user['poste'];
                $_SESSION['logged_in'] = true;
                session_regenerate_id(true);
                return true;
            }
        }
        return false;
    }
    
    public function redirectBasedOnRole() {
        if (!$this->isLoggedIn()) {
            return;
        }
        
        switch ($_SESSION['role']) {
            case ROLE_ADMIN:
                header('Location: /admin/dashboard.php');
                break;
            case ROLE_EMPLOYEE:
                header('Location: /employee/dashboard.php');
                break;
            case ROLE_CLIENT:
                header('Location: /client/index.php');
                break;
            default:
                header('Location: /index.php');
        }
        exit();
    }
    
    public function requireRole($role, $redirect = null) {
        if (!$this->checkRole($role)) {
            if ($redirect) {
                header("Location: $redirect");
            } else {
                switch($role) {
                    case ROLE_CLIENT:
                        header('Location: /client/login.php');
                        break;
                    case ROLE_ADMIN:
                        header('Location: /admin/login.php');
                        break;
                    case ROLE_EMPLOYEE:
                        header('Location: /employee/login.php');
                        break;
                    default:
                        header('Location: /index.php');
                }
            }
            exit();
        }
    }
    
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /index.php');
        exit();
    }
}

// Create global auth instance
require_once __DIR__ . '/../config/database.php';
$auth = new Auth($GLOBALS['conn']);
?>