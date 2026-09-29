<?php
// Application Configuration
define('APP_NAME', 'Hospital Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/hms');
define('BASE_PATH', dirname(__DIR__));

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Timezone
date_default_timezone_set('Africa/Accra');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Mirrors the users.role ENUM in database/schema.sql. Every role written to
// the database must come from this list, so an unknown value is rejected as a
// client error (400) instead of failing as a MySQL enum error and surfacing
// as a 500.
define('USER_ROLES', [
    'super_admin', 'admin', 'it', 'records', 'nurse', 'doctor',
    'pharmacy', 'lab', 'radiology', 'account', 'revenue'
]);

// File Upload
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Include database configuration
require_once __DIR__ . '/database.php';

// Include common functions
require_once __DIR__ . '/../includes/functions.php';

// Load model classes
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Patient.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
