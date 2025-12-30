# Setup Instructions

## Quick Start

1. **Install Dependencies**

   First, install backend dependencies:
   ```bash
   npm install
   ```

   Then install frontend dependencies:
   ```bash
   cd client
   npm install
   cd ..
   ```

   Or use the convenience script:
   ```bash
   npm run install-all
   ```

2. **Environment Configuration**

   The database connection is already configured in `server/config/database.js` with your provided credentials:
   - Host: 167.99.181.177
   - Database: bmislandhost_plan_swgoh
   - User: bmislandhost_bradley
   - Password: DragonFly$2025

   For production, create a `.env` file in the root directory with:
   ```
   PORT=5000
   DB_HOST=167.99.181.177
   DB_NAME=bmislandhost_plan_swgoh
   DB_USER=bmislandhost_bradley
   DB_PASSWORD=DragonFly$2025
   JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
   ```

3. **Start the Application**

   To run both backend and frontend:
   ```bash
   npm run dev
   ```

   Or run separately:
   - Backend: `npm run server` (runs on port 5000)
   - Frontend: `npm run client` (runs on port 3000)

4. **Access the Application**

   Open your browser and navigate to:
   ```
   http://localhost:3000
   ```

5. **First Use**

   - Register a new account on the login page
   - Once logged in, you'll see the dashboard
   - Navigate to different sections using the top menu
   - All your data will be automatically saved to the database

## Database

The application will automatically create all necessary tables on first run. No manual database setup is required.

## Troubleshooting

- **Connection Error**: Make sure your database server is accessible and credentials are correct
- **Port Already in Use**: Change the PORT in `.env` or stop the process using the port
- **Module Not Found**: Run `npm install` in both root and client directories

