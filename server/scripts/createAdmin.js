const bcrypt = require('bcryptjs');
const pool = require('../config/database');
require('dotenv').config();

async function createAdmin() {
  try {
    // Add role column if it doesn't exist
    try {
      await pool.query(`
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user'
      `);
      console.log('Role column added/verified');
    } catch (error) {
      // Column might already exist, try without IF NOT EXISTS
      try {
        await pool.query(`
          ALTER TABLE users 
          ADD COLUMN role VARCHAR(20) DEFAULT 'user'
        `);
        console.log('Role column added');
      } catch (err) {
        if (err.code !== 'ER_DUP_FIELDNAME') {
          throw err;
        }
        console.log('Role column already exists');
      }
    }

    // Get admin credentials from command line or use defaults
    const args = process.argv.slice(2);
    const username = args[0] || 'admin';
    const password = args[1] || 'admin123';

    // Check if admin already exists
    const [existing] = await pool.query(
      'SELECT id FROM users WHERE username = ?',
      [username]
    );

    if (existing.length > 0) {
      // Update existing user to admin
      const hashedPassword = await bcrypt.hash(password, 10);
      await pool.query(
        'UPDATE users SET password = ?, role = ? WHERE id = ?',
        [hashedPassword, 'admin', existing[0].id]
      );
      console.log(`\n✅ Admin user updated successfully!`);
      console.log(`Username: ${username}`);
      console.log(`Password: ${password}`);
      console.log(`\n⚠️  Please change the password after first login!\n`);
    } else {
      // Create new admin user
      const hashedPassword = await bcrypt.hash(password, 10);
      const [result] = await pool.query(
        'INSERT INTO users (username, password, role) VALUES (?, ?, ?)',
        [username, hashedPassword, 'admin']
      );
      console.log(`\n✅ Admin user created successfully!`);
      console.log(`Username: ${username}`);
      console.log(`Password: ${password}`);
      console.log(`User ID: ${result.insertId}`);
      console.log(`\n⚠️  Please change the password after first login!\n`);
    }

    process.exit(0);
  } catch (error) {
    console.error('Error creating admin user:', error);
    process.exit(1);
  }
}

createAdmin();

