import React from 'react';
import { Link, useLocation, Outlet } from 'react-router-dom';
import './Dashboard.css';

function Dashboard({ onLogout, userRole }) {
  const location = useLocation();
  const isAdmin = userRole === 'admin';

  return (
    <div className="dashboard-container">
      <nav className="dashboard-nav">
        <div className="nav-brand">
          <h1>SWGOH Planner</h1>
        </div>
        <div className="nav-links">
          <Link
            to="/homepage"
            className={location.pathname === '/homepage' ? 'active' : ''}
          >
            Homepage
          </Link>
          <Link
            to="/gac"
            className={location.pathname === '/gac' ? 'active' : ''}
          >
            GAC Planner
          </Link>
          <Link
            to="/guild"
            className={location.pathname === '/guild' ? 'active' : ''}
          >
            Guild Planner
          </Link>
          <Link
            to="/journey"
            className={location.pathname === '/journey' ? 'active' : ''}
          >
            Journey Tracker
          </Link>
          <Link
            to="/roster"
            className={location.pathname === '/roster' ? 'active' : ''}
          >
            Roster Planner
          </Link>
          <Link
            to="/gear"
            className={location.pathname === '/gear' ? 'active' : ''}
          >
            Gear/Relic Planner
          </Link>
          <Link
            to="/settings"
            className={location.pathname === '/settings' ? 'active' : ''}
          >
            Settings
          </Link>
          {isAdmin && (
            <Link
              to="/admin"
              className={location.pathname === '/admin' ? 'active' : ''}
            >
              Admin
            </Link>
          )}
        </div>
        <button onClick={onLogout} className="logout-button">
          Logout
        </button>
      </nav>
      <main className="dashboard-content">
        <Outlet />
      </main>
    </div>
  );
}

export default Dashboard;

