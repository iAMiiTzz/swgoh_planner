<?php
// Database Configuration
define('DB_HOST', '167.99.181.177');
define('DB_NAME', 'bmislandhost_plan_swgoh');
define('DB_USER', 'bmislandhost_bradley');
define('DB_PASS', 'DragonFly$2025');

// Create database connection
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Initialize database tables
function initializeDatabase() {
    $conn = getDB();
    
    // Create users table
    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            main_ally_code VARCHAR(15) NULL,
            alt_ally_code VARCHAR(15) NULL,
            extra_ally_code VARCHAR(15) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Add columns if they don't exist
    $columns = ['role', 'main_ally_code', 'alt_ally_code', 'extra_ally_code'];
    foreach ($columns as $column) {
        $result = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($result->num_rows == 0) {
            if ($column === 'role') {
                $conn->query("ALTER TABLE users ADD COLUMN $column VARCHAR(20) DEFAULT 'user'");
            } else {
                $conn->query("ALTER TABLE users ADD COLUMN $column VARCHAR(15) NULL");
            }
        }
    }
    
    // Create GAC plans table
    $conn->query("
        CREATE TABLE IF NOT EXISTS gac_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_name VARCHAR(100) NOT NULL,
            league VARCHAR(50) DEFAULT 'kyber',
            format VARCHAR(10) DEFAULT '5v5',
            defense_teams JSON,
            offense_teams JSON,
            fleet_teams JSON,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create journey tracker table
    $conn->query("
        CREATE TABLE IF NOT EXISTS journey_tracker (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            journey_name VARCHAR(100) NOT NULL,
            character_name VARCHAR(100) NOT NULL,
            current_stage INT DEFAULT 0,
            total_stages INT NOT NULL,
            unlocked BOOLEAN DEFAULT FALSE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create roster table
    $conn->query("
        CREATE TABLE IF NOT EXISTS roster (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            character_name VARCHAR(100) NOT NULL,
            star_level INT DEFAULT 1,
            gear_level INT DEFAULT 1,
            relic_level INT DEFAULT 0,
            zeta_count INT DEFAULT 0,
            omicron_count INT DEFAULT 0,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_character (user_id, character_name)
        )
    ");
    
    // Create gear farming table
    $conn->query("
        CREATE TABLE IF NOT EXISTS gear_farming (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            character_name VARCHAR(100),
            gear_name VARCHAR(100) NOT NULL,
            gear_type ENUM('gear', 'relic') DEFAULT 'gear',
            target_quantity INT NOT NULL,
            current_quantity INT DEFAULT 0,
            priority INT DEFAULT 5,
            farming_location VARCHAR(200),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create guild planner table
    $conn->query("
        CREATE TABLE IF NOT EXISTS guild_planner (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_name VARCHAR(255) NOT NULL,
            guild_name VARCHAR(255),
            event_type VARCHAR(50),
            teams JSON,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
}

// Initialize on first load
initializeDatabase();
?>

