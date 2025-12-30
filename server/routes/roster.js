const express = require('express');
const pool = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

router.use(authenticateToken);

// Get all roster characters
router.get('/', async (req, res) => {
  try {
    const [characters] = await pool.query(
      'SELECT * FROM roster WHERE user_id = ? ORDER BY character_name',
      [req.user.id]
    );
    res.json(characters);
  } catch (error) {
    console.error('Error fetching roster:', error);
    res.status(500).json({ error: 'Error fetching roster' });
  }
});

// Get single character
router.get('/:id', async (req, res) => {
  try {
    const [characters] = await pool.query(
      'SELECT * FROM roster WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );
    if (characters.length === 0) {
      return res.status(404).json({ error: 'Character not found' });
    }
    res.json(characters[0]);
  } catch (error) {
    console.error('Error fetching character:', error);
    res.status(500).json({ error: 'Error fetching character' });
  }
});

// Create or update character
router.post('/', async (req, res) => {
  try {
    const { character_name, star_level, gear_level, relic_level, zeta_count, omicron_count, notes } = req.body;

    if (!character_name) {
      return res.status(400).json({ error: 'Character name is required' });
    }

    // Check if character exists
    const [existing] = await pool.query(
      'SELECT id FROM roster WHERE user_id = ? AND character_name = ?',
      [req.user.id, character_name]
    );

    if (existing.length > 0) {
      // Update existing
      const [result] = await pool.query(
        'UPDATE roster SET star_level = ?, gear_level = ?, relic_level = ?, zeta_count = ?, omicron_count = ?, notes = ? WHERE id = ? AND user_id = ?',
        [
          star_level || 1,
          gear_level || 1,
          relic_level || 0,
          zeta_count || 0,
          omicron_count || 0,
          notes || '',
          existing[0].id,
          req.user.id
        ]
      );
      res.json({ id: existing[0].id, message: 'Character updated successfully' });
    } else {
      // Create new
      const [result] = await pool.query(
        'INSERT INTO roster (user_id, character_name, star_level, gear_level, relic_level, zeta_count, omicron_count, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
          req.user.id,
          character_name,
          star_level || 1,
          gear_level || 1,
          relic_level || 0,
          zeta_count || 0,
          omicron_count || 0,
          notes || ''
        ]
      );
      res.status(201).json({ id: result.insertId, message: 'Character added successfully' });
    }
  } catch (error) {
    console.error('Error adding/updating character:', error);
    res.status(500).json({ error: 'Error adding/updating character' });
  }
});

// Update character
router.put('/:id', async (req, res) => {
  try {
    const { character_name, star_level, gear_level, relic_level, zeta_count, omicron_count, notes } = req.body;

    const [result] = await pool.query(
      'UPDATE roster SET character_name = ?, star_level = ?, gear_level = ?, relic_level = ?, zeta_count = ?, omicron_count = ?, notes = ? WHERE id = ? AND user_id = ?',
      [
        character_name,
        star_level,
        gear_level,
        relic_level,
        zeta_count,
        omicron_count,
        notes || '',
        req.params.id,
        req.user.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Character not found' });
    }

    res.json({ message: 'Character updated successfully' });
  } catch (error) {
    console.error('Error updating character:', error);
    res.status(500).json({ error: 'Error updating character' });
  }
});

// Delete character
router.delete('/:id', async (req, res) => {
  try {
    const [result] = await pool.query(
      'DELETE FROM roster WHERE id = ? AND user_id = ?',
      [req.params.id, req.user.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Character not found' });
    }

    res.json({ message: 'Character deleted successfully' });
  } catch (error) {
    console.error('Error deleting character:', error);
    res.status(500).json({ error: 'Error deleting character' });
  }
});

module.exports = router;

