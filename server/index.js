const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
const path = require('path');
const fs = require('fs');

dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// API Routes
app.use('/api/auth', require('./routes/auth'));
app.use('/api/gac', require('./routes/gac'));
app.use('/api/journey', require('./routes/journey'));
app.use('/api/roster', require('./routes/roster'));
app.use('/api/gear', require('./routes/gear'));
app.use('/api/guild', require('./routes/guild'));
app.use('/api/swgoh', require('./routes/swgoh'));
app.use('/api/admin', require('./routes/admin'));

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', message: 'SWGOH Planner API is running' });
});

// Serve static files from the React app in production
if (process.env.NODE_ENV === 'production') {
  const buildPath = path.join(__dirname, '../client/build');
  const indexPath = path.join(buildPath, 'index.html');
  
  console.log('Production mode: Checking build folder...');
  console.log('Build path:', buildPath);
  console.log('Index.html path:', indexPath);
  
  // Check if build folder exists
  if (fs.existsSync(buildPath)) {
    console.log('✓ Build folder exists');
    if (fs.existsSync(indexPath)) {
      console.log('✓ index.html exists');
    } else {
      console.error('✗ index.html NOT FOUND at:', indexPath);
    }
  } else {
    console.error('✗ Build folder NOT FOUND at:', buildPath);
    console.error('Make sure the React app was built before starting the server!');
  }
  
  // Serve static files
  app.use(express.static(buildPath));
  
  // Handle React routing, return all requests to React app
  app.get('*', (req, res) => {
    // Don't serve index.html for API routes
    if (req.path.startsWith('/api')) {
      return res.status(404).json({ error: 'API endpoint not found' });
    }
    
    if (fs.existsSync(indexPath)) {
      res.sendFile(indexPath);
    } else {
      res.status(500).send(`
        <html>
          <body style="font-family: Arial; padding: 40px; text-align: center;">
            <h1>Build Error</h1>
            <p>The React app build files were not found.</p>
            <p>Please ensure the build completed successfully.</p>
            <p>Build path: ${buildPath}</p>
          </body>
        </html>
      `);
    }
  });
}

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  if (process.env.NODE_ENV === 'production') {
    console.log('Production mode: Serving React build files');
  }
});

