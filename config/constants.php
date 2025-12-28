<?php
// config/constants.php

// Application Constants
define('APP_NAME', 'Car Rental System');
define('APP_VERSION', '1.0.0');

// Currency
define('CURRENCY', '€');  // Euro symbol
define('CURRENCY_CODE', 'EUR');
define('CURRENCY_POSITION', 'after'); // 'before' or 'after'

// Path Constants
define('BASE_URL', 'http://localhost:8000');
define('ASSETS_PATH', '/assets/');
define('UPLOADS_PATH', '/uploads/');

// Database Constants (for reference, actual in database.php)
define('DB_HOST', '172.21.192.1');
define('DB_NAME', 'car_rental');
define('DB_USER', 'wsl_user');
define('DB_PASS', 'wsl_password');

// Session Constants
define('SESSION_NAME', 'CAR_RENTAL_SESSION');
define('SESSION_LIFETIME', 86400); // 24 hours in seconds

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_EMPLOYEE', 'employee');
define('ROLE_CLIENT', 'client');

// Car Status
define('CAR_AVAILABLE', 'available');
define('CAR_RENTED', 'rented');
define('CAR_MAINTENANCE', 'maintenance');

// Reservation Status
define('RESERVATION_PENDING', 'pending');
define('RESERVATION_CONFIRMED', 'confirmed');
define('RESERVATION_CANCELLED', 'cancelled');
define('RESERVATION_COMPLETED', 'completed');

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Security
define('PASSWORD_MIN_LENGTH', 8);
?>