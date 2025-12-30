const express = require('express');
const pool = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

// All routes require authentication
router.use(authenticateToken);

// Get all users with ally codes
router.get('/users', async (req, res) => {
  try {
    const [users] = await pool.query(
      'SELECT id, username, main_ally_code, alt_ally_code, extra_ally_code FROM users WHERE (main_ally_code IS NOT NULL OR alt_ally_code IS NOT NULL OR extra_ally_code IS NOT NULL) ORDER BY username ASC'
    );
    res.json(users);
  } catch (error) {
    console.error('Error fetching users:', error);
    res.status(500).json({ error: 'Error fetching users' });
  }
});

// Get single guild plan
router.get('/:id', async (req, res) => {
  try {
    const [plans] = await pool.query(
      'SELECT * FROM guild_planner WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (plans.length === 0) {
      return res.status(404).json({ error: 'Guild plan not found' });
    }
    res.json(plans[0]);
  } catch (error) {
    console.error('Error fetching guild plan:', error);
    res.status(500).json({ error: 'Error fetching guild plan' });
  }
});

// Create guild plan
router.post('/', async (req, res) => {
  try {
    const { plan_name, guild_name, event_type, teams, notes } = req.body;

    if (!plan_name) {
      return res.status(400).json({ error: 'Plan name is required' });
    }

    const [result] = await pool.query(
      'INSERT INTO guild_planner (user_id, plan_name, guild_name, event_type, teams, notes) VALUES (?, ?, ?, ?, ?, ?)',
      [
        req.user.id,
        plan_name,
        guild_name || '',
        event_type || '',
        JSON.stringify(teams || []),
        notes || ''
      ]
    );

    res.status(201).json({ id: result.insertId, message: 'Guild plan created successfully' });
  } catch (error) {
    console.error('Error creating guild plan:', error);
    res.status(500).json({ error: 'Error creating guild plan' });
  }
});

// Update guild plan
router.put('/:id', async (req, res) => {
  try {
    const { plan_name, guild_name, event_type, teams, notes } = req.body;

    const [result] = await pool.query(
      'UPDATE guild_planner SET plan_name = ?, guild_name = ?, event_type = ?, teams = ?, notes = ? WHERE id = ? AND user_id = ?',
      [
        plan_name,
        guild_name || '',
        event_type || '',
        JSON.stringify(teams || []),
        notes || '',
        req.params.id,
        req.user.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Guild plan not found' });
    }

    res.json({ message: 'Guild plan updated successfully' });
  } catch (error) {
    console.error('Error updating guild plan:', error);
    res.status(500).json({ error: 'Error updating guild plan' });
  }
});

// Delete guild plan
router.delete('/:id', async (req, res) => {
  try {
    const [result] = await pool.query(
      'DELETE FROM guild_planner WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Guild plan not found' });
    }

    res.json({ message: 'Guild plan deleted successfully' });
  } catch (error) {
    console.error('Error deleting guild plan:', error);
    res.status(500).json({ error: 'Error deleting guild plan' });
  }
});

module.exports = router;

