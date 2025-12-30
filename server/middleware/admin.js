const pool = require('../config/database');

const isAdmin = async (req, res, next) => {
  try {
    // req.user should already be set by authenticateToken middleware
    if (!req.user || !req.user.id) {
      return res.status(401).json({ error: 'Authentication required' });
    }

    // Get user from database to check role
    const [users] = await pool.query(
      'SELECT role FROM users WHERE id = ?',
      [req.user.id]
    );

    if (users.length === 0 || users[0].role !== 'admin') {
      return res.status(403).json({ error: 'Admin access required' });
    }

    req.user.role = users[0].role;
    next();
  } catch (error) {
    console.error('Admin middleware error:', error);
    res.status(403).json({ error: 'Admin access required' });
  }
};

module.exports = { isAdmin };

