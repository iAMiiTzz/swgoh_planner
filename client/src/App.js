import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './components/Login';
import Dashboard from './components/Dashboard';
import Homepage from './components/Homepage';
import DashboardHome from './components/DashboardHome';
import GACPlanner from './components/GACPlanner';
import GuildPlanner from './components/GuildPlanner';
import JourneyTracker from './components/JourneyTracker';
import RosterPlanner from './components/RosterPlanner';
import GearPlanner from './components/GearPlanner';
import Settings from './components/Settings';
import AdminPanel from './components/AdminPanel';
import ErrorBoundary from './components/ErrorBoundary';
import { getAuthToken, setAuthToken, removeAuthToken } from './utils/auth';
import api from './utils/api';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userRole, setUserRole] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = getAuthToken();
    if (token) {
      // Verify token using API utility
      api.get('/auth/verify')
        .then((response) => {
          setIsAuthenticated(true);
          setUserRole(response.data.user?.role || 'user');
        })
        .catch(() => {
          removeAuthToken();
        })
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const handleLogin = (token) => {
    setAuthToken(token);
    setIsAuthenticated(true);
    // Get user role after login
    api.get('/auth/verify')
      .then((response) => {
        setUserRole(response.data.user?.role || 'user');
      })
      .catch(() => {
        setUserRole('user');
      });
  };

  const handleLogout = () => {
    removeAuthToken();
    setIsAuthenticated(false);
    setUserRole(null);
  };

  if (loading) {
    return <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', color: 'white' }}>Loading...</div>;
  }

  return (
    <ErrorBoundary>
      <Router>
        <Routes>
          <Route
            path="/login"
            element={
              isAuthenticated ? (
                <Navigate to="/homepage" replace />
              ) : (
                <Login onLogin={handleLogin} />
              )
            }
          />
          <Route
            path="/"
            element={
              isAuthenticated ? (
                <Dashboard onLogout={handleLogout} userRole={userRole} />
              ) : (
                <Navigate to="/login" replace />
              )
            }
          >
            <Route index element={<Navigate to="/homepage" replace />} />
            <Route path="homepage" element={<Homepage />} />
            <Route path="dashboard" element={<DashboardHome />} />
            <Route path="gac" element={<GACPlanner />} />
            <Route path="guild" element={<GuildPlanner />} />
            <Route path="journey" element={<JourneyTracker />} />
            <Route path="roster" element={<RosterPlanner />} />
            <Route path="gear" element={<GearPlanner />} />
            <Route path="settings" element={<Settings />} />
            <Route path="admin" element={<AdminPanel />} />
          </Route>
        </Routes>
      </Router>
    </ErrorBoundary>
  );
}

export default App;

