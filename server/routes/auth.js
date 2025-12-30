const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const pool = require('../config/database');
const { authenticateToken, JWT_SECRET } = require('../middleware/auth');

const router = express.Router();

// Register
router.post('/register', async (req, res) => {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }

    // Check if user exists
    const [existingUsers] = await pool.query(
      'SELECT id FROM users WHERE username = ?',
      [username]
    );

    if (existingUsers.length > 0) {
      return res.status(400).json({ error: 'Username already exists' });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insert user
    const [result] = await pool.query(
      'INSERT INTO users (username, password) VALUES (?, ?)',
      [username, hashedPassword]
    );

    // Generate token
    const token = jwt.sign(
      { id: result.insertId, username },
      JWT_SECRET,
      { expiresIn: '7d' }
    );

    res.status(201).json({
      message: 'User created successfully',
      token,
      user: { id: result.insertId, username }
    });
  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({ error: 'Server error during registration' });
  }
});

// Login
router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }

    // Find user
    const [users] = await pool.query(
      'SELECT * FROM users WHERE username = ?',
      [username]
    );

    if (users.length === 0) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const user = users[0];

    // Verify password
    const isValidPassword = await bcrypt.compare(password, user.password);

    if (!isValidPassword) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    // Generate token
    const token = jwt.sign(
      { id: user.id, username: user.username, role: user.role || 'user' },
      JWT_SECRET,
      { expiresIn: '7d' }
    );

    res.json({
      message: 'Login successful',
      token,
      user: { id: user.id, username: user.username, role: user.role || 'user' }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Server error during login' });
  }
});

// Verify token
router.get('/verify', authenticateToken, async (req, res) => {
  try {
    // Get user role from database
    const [users] = await pool.query(
      'SELECT id, username, role, main_ally_code, alt_ally_code, extra_ally_code FROM users WHERE id = ?',
      [req.user.id]
    );
    
    if (users.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }
    
    const user = users[0];
    res.json({ 
      user: { 
        id: user.id, 
        username: user.username, 
        role: user.role || 'user',
        main_ally_code: user.main_ally_code || '',
        alt_ally_code: user.alt_ally_code || '',
        extra_ally_code: user.extra_ally_code || ''
      } 
    });
  } catch (error) {
    console.error('Verify error:', error);
    res.status(500).json({ error: 'Error verifying token' });
  }
});

// Change password
router.post('/change-password', authenticateToken, async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;

    if (!currentPassword || !newPassword) {
      return res.status(400).json({ error: 'Current password and new password are required' });
    }

    if (newPassword.length < 6) {
      return res.status(400).json({ error: 'New password must be at least 6 characters long' });
    }

    // Get user from database
    const [users] = await pool.query(
      'SELECT * FROM users WHERE id = ?',
      [req.user.id]
    );

    if (users.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }

    const user = users[0];

    // Verify current password
    const isValidPassword = await bcrypt.compare(currentPassword, user.password);

    if (!isValidPassword) {
      return res.status(401).json({ error: 'Current password is incorrect' });
    }

    // Hash new password
    const hashedPassword = await bcrypt.hash(newPassword, 10);

    // Update password
    await pool.query(
      'UPDATE users SET password = ? WHERE id = ?',
      [hashedPassword, req.user.id]
    );

    res.json({ message: 'Password changed successfully' });
  } catch (error) {
    console.error('Password change error:', error);
    res.status(500).json({ error: 'Server error during password change' });
  }
});

// Change username
router.post('/change-username', authenticateToken, async (req, res) => {
  try {
    const { newUsername, password } = req.body;

    if (!newUsername || !password) {
      return res.status(400).json({ error: 'New username and password are required' });
    }

    if (newUsername.length < 3) {
      return res.status(400).json({ error: 'Username must be at least 3 characters long' });
    }

    if (newUsername.length > 50) {
      return res.status(400).json({ error: 'Username must be less than 50 characters' });
    }

    // Get user from database
    const [users] = await pool.query(
      'SELECT * FROM users WHERE id = ?',
      [req.user.id]
    );

    if (users.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }

    const user = users[0];

    // Verify password
    const isValidPassword = await bcrypt.compare(password, user.password);

    if (!isValidPassword) {
      return res.status(401).json({ error: 'Password is incorrect' });
    }

    // Check if new username already exists
    const [existingUsers] = await pool.query(
      'SELECT id FROM users WHERE username = ? AND id != ?',
      [newUsername, req.user.id]
    );

    if (existingUsers.length > 0) {
      return res.status(400).json({ error: 'Username already exists' });
    }

    // Check if username is the same
    if (user.username === newUsername) {
      return res.status(400).json({ error: 'New username must be different from current username' });
    }

    // Update username
    await pool.query(
      'UPDATE users SET username = ? WHERE id = ?',
      [newUsername, req.user.id]
    );

    // Generate new token with updated username
    const token = jwt.sign(
      { id: user.id, username: newUsername, role: user.role || 'user' },
      JWT_SECRET,
      { expiresIn: '7d' }
    );

    res.json({ 
      message: 'Username changed successfully',
      token,
      user: { id: user.id, username: newUsername, role: user.role || 'user' }
    });
  } catch (error) {
    console.error('Username change error:', error);
    res.status(500).json({ error: 'Server error during username change' });
  }
});

// Update ally codes
router.post('/update-ally-codes', authenticateToken, async (req, res) => {
  try {
    const { main_ally_code, alt_ally_code, extra_ally_code } = req.body;

    // Validate ally codes (should be numeric, 9 digits)
    const validateAllyCode = (code) => {
      if (!code || code.trim() === '') return true; // Allow empty
      const cleaned = code.replace(/-/g, ''); // Remove dashes
      return /^\d{9}$/.test(cleaned);
    };

    if (main_ally_code && !validateAllyCode(main_ally_code)) {
      return res.status(400).json({ error: 'Main ally code must be 9 digits' });
    }
    if (alt_ally_code && !validateAllyCode(alt_ally_code)) {
      return res.status(400).json({ error: 'Alt ally code must be 9 digits' });
    }
    if (extra_ally_code && !validateAllyCode(extra_ally_code)) {
      return res.status(400).json({ error: 'Extra ally code must be 9 digits' });
    }

    // Format ally codes (remove dashes, store as clean 9 digits)
    const formatAllyCode = (code) => {
      if (!code || code.trim() === '') return null;
      return code.replace(/-/g, '').trim();
    };

    // Update ally codes
    await pool.query(
      'UPDATE users SET main_ally_code = ?, alt_ally_code = ?, extra_ally_code = ? WHERE id = ?',
      [
        formatAllyCode(main_ally_code),
        formatAllyCode(alt_ally_code),
        formatAllyCode(extra_ally_code),
        req.user.id
      ]
    );

    res.json({ 
      message: 'Ally codes updated successfully',
      ally_codes: {
        main_ally_code: formatAllyCode(main_ally_code) || '',
        alt_ally_code: formatAllyCode(alt_ally_code) || '',
        extra_ally_code: formatAllyCode(extra_ally_code) || ''
      }
    });
  } catch (error) {
    console.error('Ally codes update error:', error);
    res.status(500).json({ error: 'Server error during ally codes update' });
  }
});

module.exports = router;

