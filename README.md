# SWGOH Planner

A comprehensive web application for managing your Star Wars: Galaxy of Heroes account, featuring GAC planning, journey tracking, roster management, and gear/relic farming organization.

## Features

- **GAC Planner**: Plan your defense and offense teams for Grand Arena Championship matches
- **Journey Tracker**: Track progress through character journey events
- **Roster Planner**: Manage your character roster with star levels, gear, relics, and abilities
- **Gear/Relic Planner**: Organize your farming priorities with progress tracking
- **User Authentication**: Secure login and registration system
- **Database Storage**: All data is stored in MySQL database

## Tech Stack

- **Backend**: Node.js with Express
- **Frontend**: React with React Router
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Tokens)

## Installation

1. Install backend dependencies:
```bash
npm install
```

2. Install frontend dependencies:
```bash
cd client
npm install
```

3. Configure environment variables:
   - The `.env` file is already configured with your database credentials
   - Make sure to change the `JWT_SECRET` in production

## Running the Application

### Development Mode

Run both backend and frontend concurrently:
```bash
npm run dev
```

Or run them separately:

Backend (from root directory):
```bash
npm run server
```

Frontend (from root directory):
```bash
npm run client
```

The backend will run on `http://localhost:5000`
The frontend will run on `http://localhost:3000`

## Database

The application automatically creates the following tables on first run:
- `users` - User accounts
- `gac_plans` - GAC planning data
- `journey_tracker` - Journey progress tracking
- `roster` - Character roster data
- `gear_farming` - Gear and relic farming items

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `GET /api/auth/verify` - Verify JWT token

### GAC Planner
- `GET /api/gac` - Get all GAC plans
- `GET /api/gac/:id` - Get single GAC plan
- `POST /api/gac` - Create GAC plan
- `PUT /api/gac/:id` - Update GAC plan
- `DELETE /api/gac/:id` - Delete GAC plan

### Journey Tracker
- `GET /api/journey` - Get all journeys
- `GET /api/journey/:id` - Get single journey
- `POST /api/journey` - Create journey
- `PUT /api/journey/:id` - Update journey
- `DELETE /api/journey/:id` - Delete journey

### Roster Planner
- `GET /api/roster` - Get all characters
- `GET /api/roster/:id` - Get single character
- `POST /api/roster` - Create/update character
- `PUT /api/roster/:id` - Update character
- `DELETE /api/roster/:id` - Delete character

### Gear Planner
- `GET /api/gear` - Get all gear items
- `GET /api/gear/:id` - Get single gear item
- `POST /api/gear` - Create gear item
- `PUT /api/gear/:id` - Update gear item
- `DELETE /api/gear/:id` - Delete gear item

## Usage

1. Start by registering a new account or logging in
2. Navigate to different sections using the top navigation
3. Create and manage your GAC plans, journeys, roster, and gear farming items
4. All data is automatically saved to the database

## Production Deployment

Before deploying to production:
1. Change the `JWT_SECRET` in `.env` to a secure random string
2. Update CORS settings if needed
3. Build the frontend: `npm run build`
4. Configure your production server to serve the built files

## License

ISC

