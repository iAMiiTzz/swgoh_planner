const express = require('express');
const axios = require('axios');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

// All routes require authentication
router.use(authenticateToken);

const SWGOH_API_KEY = process.env.SWGOH_API_KEY || '3a8ac';
const SWGOH_API_BASE = 'https://swgoh.gg/api';

// Get all units
router.get('/units', async (req, res) => {
  try {
    const response = await axios.get(`${SWGOH_API_BASE}/units/`, {
      headers: {
        'x-gg-bot-access': SWGOH_API_KEY
      }
    });
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching units from SWGOH.gg:', error.response?.data || error.message);
    res.status(error.response?.status || 500).json({ 
      error: 'Error fetching units from SWGOH.gg API',
      details: error.response?.data || error.message
    });
  }
});

// Get specific unit by ID
router.get('/units/:id', async (req, res) => {
  try {
    const response = await axios.get(`${SWGOH_API_BASE}/units/${req.params.id}/`, {
      headers: {
        'x-gg-bot-access': SWGOH_API_KEY
      }
    });
    res.json(response.data);
  } catch (error) {
    console.error('Error fetching unit from SWGOH.gg:', error.response?.data || error.message);
    res.status(error.response?.status || 500).json({ 
      error: 'Error fetching unit from SWGOH.gg API',
      details: error.response?.data || error.message
    });
  }
});

module.exports = router;

