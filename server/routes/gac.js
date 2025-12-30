const express = require('express');
const pool = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

// All routes require authentication
router.use(authenticateToken);

// Get all GAC plans for user
router.get('/', async (req, res) => {
  try {
    const [plans] = await pool.query(
      'SELECT * FROM gac_plans WHERE user_id = ? ORDER BY updated_at DESC',
      [req.user.id]
    );
    res.json(plans);
  } catch (error) {
    console.error('Error fetching GAC plans:', error);
    res.status(500).json({ error: 'Error fetching GAC plans' });
  }
});

// Get single GAC plan
router.get('/:id', async (req, res) => {
  try {
    const [plans] = await pool.query(
      'SELECT * FROM gac_plans WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (plans.length === 0) {
      return res.status(404).json({ error: 'GAC plan not found' });
    }
    res.json(plans[0]);
  } catch (error) {
    console.error('Error fetching GAC plan:', error);
    res.status(500).json({ error: 'Error fetching GAC plan' });
  }
});

// Create GAC plan
router.post('/', async (req, res) => {
  try {
    const { plan_name, league, format, defense_teams, offense_teams, fleet_teams, notes } = req.body;

    if (!plan_name) {
      return res.status(400).json({ error: 'Plan name is required' });
    }

    const [result] = await pool.query(
      'INSERT INTO gac_plans (user_id, plan_name, league, format, defense_teams, offense_teams, fleet_teams, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [
        req.user.id,
        plan_name,
        league || 'kyber',
        format || '5v5',
        JSON.stringify(defense_teams || []),
        JSON.stringify(offense_teams || []),
        JSON.stringify(fleet_teams || []),
        notes || ''
      ]
    );

    res.status(201).json({ id: result.insertId, message: 'GAC plan created successfully' });
  } catch (error) {
    console.error('Error creating GAC plan:', error);
    res.status(500).json({ error: 'Error creating GAC plan' });
  }
});

// Update GAC plan
router.put('/:id', async (req, res) => {
  try {
    const { plan_name, league, format, defense_teams, offense_teams, fleet_teams, notes } = req.body;

    const [result] = await pool.query(
      'UPDATE gac_plans SET plan_name = ?, league = ?, format = ?, defense_teams = ?, offense_teams = ?, fleet_teams = ?, notes = ? WHERE id = ? AND user_id = ?',
      [
        plan_name,
        league || 'kyber',
        format || '5v5',
        JSON.stringify(defense_teams || []),
        JSON.stringify(offense_teams || []),
        JSON.stringify(fleet_teams || []),
        notes || '',
        req.params.id,
        req.user.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'GAC plan not found' });
    }

    res.json({ message: 'GAC plan updated successfully' });
  } catch (error) {
    console.error('Error updating GAC plan:', error);
    res.status(500).json({ error: 'Error updating GAC plan' });
  }
});

// Delete GAC plan
router.delete('/:id', async (req, res) => {
  try {
    const [result] = await pool.query(
      'DELETE FROM gac_plans WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'GAC plan not found' });
    }

    res.json({ message: 'GAC plan deleted successfully' });
  } catch (error) {
    console.error('Error deleting GAC plan:', error);
    res.status(500).json({ error: 'Error deleting GAC plan' });
  }
});

module.exports = router;

