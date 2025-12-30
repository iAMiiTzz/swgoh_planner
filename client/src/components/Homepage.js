import React from 'react';
import { Link } from 'react-router-dom';
import './Homepage.css';

function Homepage() {
  return (
    <div className="homepage-container">
      <div className="homepage-hero">
        <h1>Welcome to SWGOH Planner</h1>
        <p className="hero-subtitle">Your comprehensive tool for managing your Star Wars: Galaxy of Heroes account</p>
      </div>
      
      <div className="homepage-content">
        <div className="features-section">
          <h2>Features</h2>
          <div className="feature-grid">
            <Link to="/gac" className="feature-card">
              <div className="feature-icon">⚔️</div>
              <h3>GAC Planner</h3>
              <p>Plan your defense and offense teams for Grand Arena Championship matches.</p>
            </Link>
            
            <Link to="/guild" className="feature-card">
              <div className="feature-icon">🏰</div>
              <h3>Guild Planner</h3>
              <p>Organize guild events, coordinate teams, and plan guild activities.</p>
            </Link>
            
            <Link to="/journey" className="feature-card">
              <div className="feature-icon">🗺️</div>
              <h3>Journey Tracker</h3>
              <p>Track your progress through character journey events and unlock requirements.</p>
            </Link>
            
            <Link to="/roster" className="feature-card">
              <div className="feature-icon">👥</div>
              <h3>Roster Planner</h3>
              <p>Manage your character roster, track star levels, gear, relics, and abilities.</p>
            </Link>
            
            <Link to="/gear" className="feature-card">
              <div className="feature-icon">⚙️</div>
              <h3>Gear/Relic Planner</h3>
              <p>Organize your gear and relic farming priorities with progress tracking.</p>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Homepage;

