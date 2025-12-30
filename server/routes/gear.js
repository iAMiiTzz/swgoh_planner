const express = require('express');
const pool = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

router.use(authenticateToken);

// Get all gear farming items
router.get('/', async (req, res) => {
  try {
    const [items] = await pool.query(
      'SELECT * FROM gear_farming WHERE user_id = ? ORDER BY priority DESC, created_at DESC',
      [req.user.id]
    );
    res.json(items);
  } catch (error) {
    console.error('Error fetching gear items:', error);
    res.status(500).json({ error: 'Error fetching gear items' });
  }
});

// Get single gear item
router.get('/:id', async (req, res) => {
  try {
    const [items] = await pool.query(
      'SELECT * FROM gear_farming WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (items.length === 0) {
      return res.status(404).json({ error: 'Gear item not found' });
    }
    res.json(items[0]);
  } catch (error) {
    console.error('Error fetching gear item:', error);
    res.status(500).json({ error: 'Error fetching gear item' });
  }
});

// Create gear farming item
router.post('/', async (req, res) => {
  try {
    const { character_name, gear_name, gear_type, target_quantity, current_quantity, priority, farming_location, notes } = req.body;

    if (!gear_name || !target_quantity) {
      return res.status(400).json({ error: 'Gear name and target quantity are required' });
    }

    const [result] = await pool.query(
      'INSERT INTO gear_farming (user_id, character_name, gear_name, gear_type, target_quantity, current_quantity, priority, farming_location, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [
        req.user.id,
        character_name || null,
        gear_name,
        gear_type || 'gear',
        target_quantity,
        current_quantity || 0,
        priority || 5,
        farming_location || '',
        notes || ''
      ]
    );

    res.status(201).json({ id: result.insertId, message: 'Gear item created successfully' });
  } catch (error) {
    console.error('Error creating gear item:', error);
    res.status(500).json({ error: 'Error creating gear item' });
  }
});

// Update gear farming item
router.put('/:id', async (req, res) => {
  try {
    const { character_name, gear_name, gear_type, target_quantity, current_quantity, priority, farming_location, notes } = req.body;

    const [result] = await pool.query(
      'UPDATE gear_farming SET character_name = ?, gear_name = ?, gear_type = ?, target_quantity = ?, current_quantity = ?, priority = ?, farming_location = ?, notes = ? WHERE id = ? AND user_id = ?',
      [
        character_name || null,
        gear_name,
        gear_type,
        target_quantity,
        current_quantity,
        priority,
        farming_location || '',
        notes || '',
        req.params.id,
        req.user.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Gear item not found' });
    }

    res.json({ message: 'Gear item updated successfully' });
  } catch (error) {
    console.error('Error updating gear item:', error);
    res.status(500).json({ error: 'Error updating gear item' });
  }
});

// Delete gear farming item
router.delete('/:id', async (req, res) => {
  try {
    const [result] = await pool.query(
      'DELETE FROM gear_farming WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Gear item not found' });
    }

    res.json({ message: 'Gear item deleted successfully' });
  } catch (error) {
    console.error('Error deleting gear item:', error);
    res.status(500).json({ error: 'Error deleting gear item' });
  }
});

module.exports = router;

