import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './GuildPlanner.css';

function GuildPlanner() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    loadUsers();
  }, []);

  const loadUsers = async () => {
    try {
      setLoading(true);
      const response = await api.get('/guild/users');
      setUsers(response.data);
    } catch (error) {
      console.error('Error loading users:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatAllyCode = (code) => {
    if (!code) return null;
    const cleaned = code.replace(/-/g, '');
    if (cleaned.length === 9) {
      return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
    }
    return code;
  };

  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
      alert('Ally code copied to clipboard!');
    }).catch(() => {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      alert('Ally code copied to clipboard!');
    });
  };

  const filteredUsers = users.filter(user =>
    user.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (user.main_ally_code && user.main_ally_code.includes(searchTerm)) ||
    (user.alt_ally_code && user.alt_ally_code.includes(searchTerm)) ||
    (user.extra_ally_code && user.extra_ally_code.includes(searchTerm))
  );

  if (loading) {
    return (
      <div className="guild-planner-container">
        <div className="loading-spinner">Loading users...</div>
      </div>
    );
  }

  return (
    <div className="guild-planner-container">
      <h2>Guild Members</h2>
      <p className="subtitle">View all users and their ally codes</p>
      
      <div className="search-section">
        <input
          type="text"
          placeholder="Search by username or ally code..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="search-input"
        />
      </div>

      <div className="users-grid">
        {filteredUsers.length === 0 ? (
          <div className="no-users-message">
            {searchTerm ? 'No users found matching your search.' : 'No users with ally codes found.'}
          </div>
        ) : (
          filteredUsers.map(user => (
            <div key={user.id} className="user-card">
              <div className="user-header">
                <h3>{user.username}</h3>
              </div>
              <div className="ally-codes-list">
                {user.main_ally_code && (
                  <div className="ally-code-row">
                    <span className="ally-code-label">Main:</span>
                    <span className="ally-code-value" onClick={() => copyToClipboard(formatAllyCode(user.main_ally_code))} title="Click to copy">
                      {formatAllyCode(user.main_ally_code)}
                    </span>
                  </div>
                )}
                {user.alt_ally_code && (
                  <div className="ally-code-row">
                    <span className="ally-code-label">Alt:</span>
                    <span className="ally-code-value" onClick={() => copyToClipboard(formatAllyCode(user.alt_ally_code))} title="Click to copy">
                      {formatAllyCode(user.alt_ally_code)}
                    </span>
                  </div>
                )}
                {user.extra_ally_code && (
                  <div className="ally-code-row">
                    <span className="ally-code-label">Extra:</span>
                    <span className="ally-code-value" onClick={() => copyToClipboard(formatAllyCode(user.extra_ally_code))} title="Click to copy">
                      {formatAllyCode(user.extra_ally_code)}
                    </span>
                  </div>
                )}
                {!user.main_ally_code && !user.alt_ally_code && !user.extra_ally_code && (
                  <div className="no-ally-codes">No ally codes</div>
                )}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}

export default GuildPlanner;
