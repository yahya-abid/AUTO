<?php
require_once __DIR__ . '/../config/database.php';

class Functions {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Sanitize input
    public function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
    
    // Generate random string
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    
    // Format date
    public function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }
    
    // Format currency
    public function formatCurrency($amount) {
        return CURRENCY . number_format($amount, 2);
    }
    
    // Calculate difference between two dates in days
    public function dateDifference($date1, $date2) {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        return $interval->days;
    }
    
    // Get statistics for dashboard
    public function getDashboardStats() {
        $stats = [];
        
        // Total cars
        $sql = "SELECT COUNT(*) as total FROM car WHERE is_available = 1";
        $result = $this->conn->query($sql);
        $stats['total_cars'] = $result->fetch_assoc()['total'];
        
        // Total clients
        $sql = "SELECT COUNT(*) as total FROM client WHERE is_active = 1";
        $result = $this->conn->query($sql);
        $stats['total_clients'] = $result->fetch_assoc()['total'];
        
        // Total employees
        $sql = "SELECT COUNT(*) as total FROM employee WHERE is_active = 1";
        $result = $this->conn->query($sql);
        $stats['total_employees'] = $result->fetch_assoc()['total'];
        
        // Active rentals
        $sql = "SELECT COUNT(*) as total FROM contrat WHERE status_contrat = 'active'";
        $result = $this->conn->query($sql);
        $stats['active_rentals'] = $result->fetch_assoc()['total'];
        
        // Monthly revenue
        $sql = "SELECT SUM(montant) as total FROM paiement 
                WHERE MONTH(date_pay) = MONTH(CURRENT_DATE()) 
                AND YEAR(date_pay) = YEAR(CURRENT_DATE()) 
                AND status = 'completed'";
        $result = $this->conn->query($sql);
        $stats['monthly_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        
        return $stats;
    }
    
    // Get available cars
    public function getAvailableCars($location_id = null) {
        $sql = "SELECT c.*, l.ville, l.nom as location_name 
                FROM car c 
                JOIN location l ON c.location_id = l.location_id 
                WHERE c.is_available = 1 
                AND c.etat_car IN ('excellent', 'bon', 'moyen')";
        
        if ($location_id) {
            $sql .= " AND c.location_id = ?";
        }
        
        $sql .= " ORDER BY c.marque, c.model";
        
        if ($location_id) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $location_id);
            $stmt->execute();
            return $stmt->get_result();
        }
        
        return $this->conn->query($sql);
    }
    
    // Get client reservations
    public function getClientReservations($client_id) {
        $sql = "SELECT r.*, c.matriculation, CONCAT(c.marque, ' ', c.model) as car_name,
                       l.ville as location_city
                FROM reservation r
                JOIN car c ON r.car_id = c.car_id
                JOIN location l ON c.location_id = l.location_id
                WHERE r.client_id = ?
                ORDER BY r.date_debut DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Create reservation
    public function createReservation($client_id, $car_id, $date_debut, $date_fin) {
        // Check if car is available
        $check_sql = "SELECT is_available FROM car WHERE car_id = ?";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $car = $result->fetch_assoc();
            if (!$car['is_available']) {
                return ['success' => false, 'message' => 'Car is not available'];
            }
        }
        
        // Create reservation
        $reservation_number = 'RES-' . date('Ymd') . '-' . $this->generateRandomString(6);
        
        $sql = "INSERT INTO reservation (
                    reservation_number, client_id, car_id, 
                    date_debut, date_fin, status
                ) VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiss", $reservation_number, $client_id, $car_id, $date_debut, $date_fin);
        
        if ($stmt->execute()) {
            // Update car availability
            $update_sql = "UPDATE car SET is_available = 0 WHERE car_id = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("i", $car_id);
            $update_stmt->execute();
            
            return ['success' => true, 'reservation_id' => $stmt->insert_id];
        }
        
        return ['success' => false, 'message' => 'Failed to create reservation'];
    }
    
    // Calculate age from birth date
    public function calculateAge($birth_date) {
        $birthday = new DateTime($birth_date);
        $today = new DateTime('today');
        return $birthday->diff($today)->y;
    }
    
    // Validate driver's license
    public function validateLicense($license_number) {
        // Basic validation - you can expand this based on your country's format
        return preg_match('/^[A-Z0-9]{8,20}$/', $license_number);
    }
    
    // Calculate rental duration in days
    public function calculateRentalDuration($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        return $interval->days + 1; // Include both start and end days
    }
    
    // Calculate total rental price
    public function calculateTotalPrice($daily_rate, $start_date, $end_date) {
        $days = $this->calculateRentalDuration($start_date, $end_date);
        return $daily_rate * $days;
    }
    
    // Send notification (placeholder - implement actual notification system)
    public function sendNotification($user_id, $user_type, $title, $message) {
        $sql = "INSERT INTO notification (user_id, user_type, title, message) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $user_type, $title, $message);
        return $stmt->execute();
    }
}

// Initialize Functions
$functions = new Functions($conn);
?>