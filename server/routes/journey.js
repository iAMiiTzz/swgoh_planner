const express = require('express');
const pool = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

router.use(authenticateToken);

// Get all journey trackers
router.get('/', async (req, res) => {
  try {
    const [journeys] = await pool.query(
      'SELECT * FROM journey_tracker WHERE user_id = ? ORDER BY journey_name, character_name',
      [req.user.id]
    );
    res.json(journeys);
  } catch (error) {
    console.error('Error fetching journeys:', error);
    res.status(500).json({ error: 'Error fetching journeys' });
  }
});

// Get single journey
router.get('/:id', async (req, res) => {
  try {
    const [journeys] = await pool.query(
      'SELECT * FROM journey_tracker WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (journeys.length === 0) {
      return res.status(404).json({ error: 'Journey not found' });
    }
    res.json(journeys[0]);
  } catch (error) {
    console.error('Error fetching journey:', error);
    res.status(500).json({ error: 'Error fetching journey' });
  }
});

// Create journey tracker
router.post('/', async (req, res) => {
  try {
    const { journey_name, character_name, current_stage, total_stages, unlocked, notes } = req.body;

    if (!journey_name || !character_name || !total_stages) {
      return res.status(400).json({ error: 'Journey name, character name, and total stages are required' });
    }

    const [result] = await pool.query(
      'INSERT INTO journey_tracker (user_id, journey_name, character_name, current_stage, total_stages, unlocked, notes) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [
        req.user.id,
        journey_name,
        character_name,
        current_stage || 0,
        total_stages,
        unlocked || false,
        notes || ''
      ]
    );

    res.status(201).json({ id: result.insertId, message: 'Journey tracker created successfully' });
  } catch (error) {
    console.error('Error creating journey:', error);
    res.status(500).json({ error: 'Error creating journey tracker' });
  }
});

// Update journey tracker
router.put('/:id', async (req, res) => {
  try {
    const { journey_name, character_name, current_stage, total_stages, unlocked, notes } = req.body;

    const [result] = await pool.query(
      'UPDATE journey_tracker SET journey_name = ?, character_name = ?, current_stage = ?, total_stages = ?, unlocked = ?, notes = ? WHERE id = ? AND user_id = ?',
      [
        journey_name,
        character_name,
        current_stage,
        total_stages,
        unlocked,
        notes || '',
        req.params.id,
        req.user.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Journey not found' });
    }

    res.json({ message: 'Journey tracker updated successfully' });
  } catch (error) {
    console.error('Error updating journey:', error);
    res.status(500).json({ error: 'Error updating journey tracker' });
  }
});

// Delete journey tracker
router.delete('/:id', async (req, res) => {
  try {
    const [result] = await pool.query(
      'DELETE FROM journey_tracker WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Journey not found' });
    }

    res.json({ message: 'Journey tracker deleted successfully' });
  } catch (error) {
    console.error('Error deleting journey:', error);
    res.status(500).json({ error: 'Error deleting journey tracker' });
  }
});

module.exports = router;

