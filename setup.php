<?php
/**
 * HMS Setup Script
 * This script initializes the database and creates the default super admin account
 * Run this once after setting up Laragon
 */

echo "=== Hospital Management System Setup ===\n\n";

// Check if the MySQL server (Laragon) is running
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "✓ Connected to MySQL server\n";
} catch (PDOException $e) {
    die("✗ Failed to connect to MySQL. Make sure Laragon MySQL is running.\nError: " . $e->getMessage() . "\n");
}

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS hms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'hms_db' created or already exists\n";
} catch (PDOException $e) {
    die("✗ Failed to create database: " . $e->getMessage() . "\n");
}

// Connect to the database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hms_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to hms_db database\n";
} catch (PDOException $e) {
    die("✗ Failed to connect to hms_db: " . $e->getMessage() . "\n");
}

// Read and execute schema
$schemaFile = __DIR__ . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    die("✗ Schema file not found: $schemaFile\n");
}

echo "✓ Reading schema file...\n";

try {
    $sql = file_get_contents($schemaFile);
    
    // The schema contains circular foreign keys (users <-> departments),
    // so FK checks must be disabled while tables are created.
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database schema imported successfully\n";
} catch (PDOException $e) {
    die("✗ Failed to import schema: " . $e->getMessage() . "\n");
}

// Create default super admin account
echo "\n=== Creating Default Super Admin Account ===\n";

$username = 'admin';
$password = 'Admin@123'; // Default password - CHANGE THIS AFTER FIRST LOGIN!
$fullName = 'System Administrator';
$email = 'admin@hms.com';

// Check if admin already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$existing = $stmt->fetch();

if ($existing) {
    echo "⚠ Super admin account already exists. Skipping creation.\n";
} else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, is_active, created_at) VALUES (?, ?, ?, ?, 'super_admin', 1, NOW())");
    
    try {
        $stmt->execute([$username, $hashedPassword, $email, $fullName]);
        echo "✓ Default super admin account created\n";
        echo "  Username: $username\n";
        echo "  Password: $password\n";
        echo "  ⚠ IMPORTANT: Change this password after first login!\n";
    } catch (PDOException $e) {
        echo "✗ Failed to create admin account: " . $e->getMessage() . "\n";
    }
}

// Create uploads directory
$uploadDirs = [
    __DIR__ . '/frontend/uploads',
    __DIR__ . '/frontend/uploads/profiles',
    __DIR__ . '/frontend/uploads/documents'
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created directory: $dir\n";
    }
}

echo "\n=== Setup Complete ===\n";
echo "You can now access the system at: http://localhost/hms/frontend/index.php\n";
echo "Login with the super admin credentials created above.\n";
?>
