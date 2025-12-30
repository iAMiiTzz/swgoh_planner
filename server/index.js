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
  const buildPath = path.resolve(__dirname, '../client/build');
  const indexPath = path.resolve(buildPath, 'index.html');
  
  console.log('=== Production Mode: Static File Configuration ===');
  console.log('Current working directory:', process.cwd());
  console.log('__dirname:', __dirname);
  console.log('Build path (resolved):', buildPath);
  console.log('Index.html path (resolved):', indexPath);
  
  // Check if build folder exists
  if (fs.existsSync(buildPath)) {
    console.log('✓ Build folder exists');
    
    // List files in build folder
    try {
      const files = fs.readdirSync(buildPath);
      console.log('Files in build folder:', files.join(', '));
    } catch (err) {
      console.error('Error reading build folder:', err);
    }
    
    if (fs.existsSync(indexPath)) {
      console.log('✓ index.html exists');
    } else {
      console.error('✗ index.html NOT FOUND at:', indexPath);
    }
  } else {
    console.error('✗ Build folder NOT FOUND at:', buildPath);
    console.error('Make sure the React app was built before starting the server!');
    
    // Try alternative paths
    const altPaths = [
      path.resolve(process.cwd(), 'client/build'),
      path.resolve(process.cwd(), './client/build'),
      path.join(process.cwd(), 'client', 'build')
    ];
    
    console.log('Trying alternative paths:');
    for (const altPath of altPaths) {
      if (fs.existsSync(altPath)) {
        console.log('  Found build at:', altPath);
      }
    }
  }
  
  // Serve static files (only if build path exists)
  if (fs.existsSync(buildPath)) {
    app.use(express.static(buildPath));
    console.log('✓ Static file serving enabled');
  } else {
    console.error('✗ Static file serving DISABLED - build folder not found');
  }
  
  // Handle React routing, return all requests to React app
  app.get('*', (req, res, next) => {
    // Don't serve index.html for API routes
    if (req.path.startsWith('/api')) {
      return res.status(404).json({ error: 'API endpoint not found' });
    }
    
    // Use absolute path for sendFile
    const absolutePath = path.resolve(indexPath);
    
    if (fs.existsSync(absolutePath)) {
      res.sendFile(absolutePath, (err) => {
        if (err) {
          console.error('Error sending index.html:', err);
          res.status(500).send(`
            <html>
              <body style="font-family: Arial; padding: 40px; text-align: center;">
                <h1>Server Error</h1>
                <p>Error loading application: ${err.message}</p>
                <p>Path: ${absolutePath}</p>
              </body>
            </html>
          `);
        }
      });
    } else {
      console.error('index.html not found at:', absolutePath);
      res.status(500).send(`
        <html>
          <body style="font-family: Arial; padding: 40px; text-align: center;">
            <h1>Build Error</h1>
            <p>The React app build files were not found.</p>
            <p>Please ensure the build completed successfully.</p>
            <p>Expected path: ${absolutePath}</p>
            <p>Current working directory: ${process.cwd()}</p>
            <p>__dirname: ${__dirname}</p>
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

