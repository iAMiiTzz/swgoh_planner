const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
  host: process.env.DB_HOST || '167.99.181.177',
  database: process.env.DB_NAME || 'bmislandhost_plan_swgoh',
  user: process.env.DB_USER || 'bmislandhost_bradley',
  password: process.env.DB_PASSWORD || 'DragonFly$2025',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Test connection
pool.getConnection()
  .then(connection => {
    console.log('Database connected successfully');
    connection.release();
    initializeDatabase();
  })
  .catch(err => {
    console.error('Database connection error:', err);
  });

async function initializeDatabase() {
  try {
    // Create users table
    await pool.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    
    // Add role column if it doesn't exist (for existing tables)
    try {
      await pool.query(`
        ALTER TABLE users 
        ADD COLUMN role VARCHAR(20) DEFAULT 'user'
      `);
    } catch (error) {
      // Column already exists, ignore
      if (error.code !== 'ER_DUP_FIELDNAME') {
        console.error('Error adding role column:', error);
      }
    }

    // Add ally code columns if they don't exist
    const allyCodeColumns = ['main_ally_code', 'alt_ally_code', 'extra_ally_code'];
    for (const column of allyCodeColumns) {
      try {
        await pool.query(`
          ALTER TABLE users 
          ADD COLUMN ${column} VARCHAR(20) NULL
        `);
        console.log(`Added ${column} column`);
      } catch (error) {
        // Column already exists, ignore
        if (error.code !== 'ER_DUP_FIELDNAME') {
          console.error(`Error adding ${column} column:`, error);
        }
      }
    }

    // Remove email column if it exists (for existing tables that had email)
    try {
      // Check if email column exists
      const [columns] = await pool.query(`
        SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'email'
      `);
      
      if (columns.length > 0) {
        // If email is NOT NULL, make it nullable first
        if (columns[0].IS_NULLABLE === 'NO') {
          try {
            await pool.query(`ALTER TABLE users MODIFY COLUMN email VARCHAR(100) NULL`);
          } catch (err) {
            console.error('Error making email nullable:', err);
          }
        }
        
        // Remove unique constraint on email if it exists
        try {
          // Try to find and drop the unique constraint
          const [indexes] = await pool.query(`
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'users' 
            AND COLUMN_NAME = 'email'
            AND CONSTRAINT_NAME != 'PRIMARY'
          `);
          
          for (const index of indexes) {
            try {
              await pool.query(`ALTER TABLE users DROP INDEX ${index.CONSTRAINT_NAME}`);
            } catch (err) {
              // Try dropping as constraint
              try {
                await pool.query(`ALTER TABLE users DROP CONSTRAINT ${index.CONSTRAINT_NAME}`);
              } catch (err2) {
                // Ignore if can't drop
              }
            }
          }
        } catch (err) {
          // Index might not exist, ignore
        }
        
        // Remove email column
        await pool.query(`ALTER TABLE users DROP COLUMN email`);
        console.log('Email column removed from users table');
      }
    } catch (error) {
      // Column doesn't exist or can't be removed, ignore
      if (error.code !== 'ER_CANT_DROP_FIELD_OR_KEY' && 
          error.code !== 'ER_BAD_FIELD_ERROR' &&
          error.code !== 'ER_DUP_FIELDNAME') {
        console.error('Error removing email column:', error);
      }
    }

    // Create GAC plans table
    await pool.query(`
      CREATE TABLE IF NOT EXISTS gac_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_name VARCHAR(100) NOT NULL,
        league VARCHAR(20) DEFAULT 'kyber',
        format VARCHAR(10) DEFAULT '5v5',
        defense_teams JSON,
        offense_teams JSON,
        fleet_teams JSON,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
      )
    `);

    // Add league, format, and fleet_teams columns if they don't exist
    const gacColumns = [
      { name: 'league', type: "VARCHAR(20) DEFAULT 'kyber'" },
      { name: 'format', type: "VARCHAR(10) DEFAULT '5v5'" },
      { name: 'fleet_teams', type: 'JSON' }
    ];
    
    for (const column of gacColumns) {
      try {
        await pool.query(`ALTER TABLE gac_plans ADD COLUMN ${column.name} ${column.type}`);
        console.log(`Added ${column.name} column to gac_plans`);
      } catch (error) {
        if (error.code !== 'ER_DUP_FIELDNAME') {
          console.error(`Error adding ${column.name} column:`, error);
        }
      }
    }

    // Create journey tracker table
    await pool.query(`
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
    `);

    // Create roster table
    await pool.query(`
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
    `);

    // Create gear farming table
    await pool.query(`
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
    `);

    // Create guild planner table
    await pool.query(`
      CREATE TABLE IF NOT EXISTS guild_planner (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_name VARCHAR(100) NOT NULL,
        guild_name VARCHAR(100),
        event_type VARCHAR(50),
        teams JSON,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
      )
    `);

    console.log('Database tables initialized successfully');
  } catch (error) {
    console.error('Error initializing database:', error);
  }
}

module.exports = pool;

