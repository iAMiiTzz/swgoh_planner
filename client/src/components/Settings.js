import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import { setAuthToken } from '../utils/auth';
import './Settings.css';

function Settings() {
  const [currentUsername, setCurrentUsername] = useState('');
  const [usernameFormData, setUsernameFormData] = useState({
    newUsername: '',
    password: ''
  });
  const [passwordFormData, setPasswordFormData] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  const [usernameError, setUsernameError] = useState('');
  const [usernameSuccess, setUsernameSuccess] = useState('');
  const [passwordError, setPasswordError] = useState('');
  const [passwordSuccess, setPasswordSuccess] = useState('');
  const [loading, setLoading] = useState(false);
  const [usernameLoading, setUsernameLoading] = useState(false);
  const [allyCodeFormData, setAllyCodeFormData] = useState({
    main_ally_code: '',
    alt_ally_code: '',
    extra_ally_code: ''
  });
  const [allyCodeError, setAllyCodeError] = useState('');
  const [allyCodeSuccess, setAllyCodeSuccess] = useState('');
  const [allyCodeLoading, setAllyCodeLoading] = useState(false);

  useEffect(() => {
    // Get current username and ally codes
    api.get('/auth/verify')
      .then((response) => {
        const user = response.data.user || {};
        setCurrentUsername(user.username || '');
        // Format ally codes for display (add dashes)
        const formatAllyCode = (code) => {
          if (!code) return '';
          const cleaned = code.replace(/-/g, '');
          if (cleaned.length === 9) {
            return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
          }
          return code;
        };
        setAllyCodeFormData({
          main_ally_code: formatAllyCode(user.main_ally_code || ''),
          alt_ally_code: formatAllyCode(user.alt_ally_code || ''),
          extra_ally_code: formatAllyCode(user.extra_ally_code || '')
        });
      })
      .catch((error) => {
        console.error('Error fetching user info:', error);
      });
  }, []);

  const handleUsernameChange = (e) => {
    setUsernameFormData({
      ...usernameFormData,
      [e.target.name]: e.target.value
    });
    setUsernameError('');
    setUsernameSuccess('');
  };

  const handlePasswordChange = (e) => {
    setPasswordFormData({
      ...passwordFormData,
      [e.target.name]: e.target.value
    });
    setPasswordError('');
    setPasswordSuccess('');
  };

  const handleUsernameSubmit = async (e) => {
    e.preventDefault();
    setUsernameError('');
    setUsernameSuccess('');
    setUsernameLoading(true);

    // Validation
    if (!usernameFormData.newUsername || !usernameFormData.password) {
      setUsernameError('All fields are required');
      setUsernameLoading(false);
      return;
    }

    if (usernameFormData.newUsername.length < 3) {
      setUsernameError('Username must be at least 3 characters long');
      setUsernameLoading(false);
      return;
    }

    if (usernameFormData.newUsername === currentUsername) {
      setUsernameError('New username must be different from current username');
      setUsernameLoading(false);
      return;
    }

    try {
      const response = await api.post('/auth/change-username', {
        newUsername: usernameFormData.newUsername,
        password: usernameFormData.password
      });

      // Update token if provided
      if (response.data.token) {
        setAuthToken(response.data.token);
      }

      setUsernameSuccess('Username changed successfully!');
      setCurrentUsername(usernameFormData.newUsername);
      setUsernameFormData({
        newUsername: '',
        password: ''
      });
      
      // Reload page to update username in app state
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } catch (err) {
      setUsernameError(err.response?.data?.error || 'Error changing username');
    } finally {
      setUsernameLoading(false);
    }
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    setPasswordError('');
    setPasswordSuccess('');
    setLoading(true);

    // Validation
    if (!passwordFormData.currentPassword || !passwordFormData.newPassword || !passwordFormData.confirmPassword) {
      setPasswordError('All fields are required');
      setLoading(false);
      return;
    }

    if (passwordFormData.newPassword.length < 6) {
      setPasswordError('New password must be at least 6 characters long');
      setLoading(false);
      return;
    }

    if (passwordFormData.newPassword !== passwordFormData.confirmPassword) {
      setPasswordError('New passwords do not match');
      setLoading(false);
      return;
    }

    if (passwordFormData.currentPassword === passwordFormData.newPassword) {
      setPasswordError('New password must be different from current password');
      setLoading(false);
      return;
    }

    try {
      await api.post('/auth/change-password', {
        currentPassword: passwordFormData.currentPassword,
        newPassword: passwordFormData.newPassword
      });

      setPasswordSuccess('Password changed successfully!');
      setPasswordFormData({
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      });
    } catch (err) {
      setPasswordError(err.response?.data?.error || 'Error changing password');
    } finally {
      setLoading(false);
    }
  };

  const handleAllyCodeChange = (e) => {
    const { name, value } = e.target;
    // Remove any non-digit characters except dashes
    const cleaned = value.replace(/[^\d-]/g, '');
    // Auto-format with dashes as user types
    let formatted = cleaned.replace(/-/g, '');
    if (formatted.length > 3) {
      formatted = `${formatted.slice(0, 3)}-${formatted.slice(3)}`;
    }
    if (formatted.length > 7) {
      formatted = `${formatted.slice(0, 7)}-${formatted.slice(7, 10)}`;
    }
    // Limit to 11 characters (9 digits + 2 dashes)
    if (formatted.length > 11) {
      formatted = formatted.slice(0, 11);
    }
    
    setAllyCodeFormData({
      ...allyCodeFormData,
      [name]: formatted
    });
    setAllyCodeError('');
    setAllyCodeSuccess('');
  };

  const handleAllyCodeSubmit = async (e) => {
    e.preventDefault();
    setAllyCodeError('');
    setAllyCodeSuccess('');
    setAllyCodeLoading(true);

    try {
      await api.post('/auth/update-ally-codes', allyCodeFormData);
      setAllyCodeSuccess('Ally codes updated successfully!');
      
      // Reload user data to get formatted codes back
      const response = await api.get('/auth/verify');
      const user = response.data.user || {};
      const formatAllyCode = (code) => {
        if (!code) return '';
        const cleaned = code.replace(/-/g, '');
        if (cleaned.length === 9) {
          return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
        }
        return code;
      };
      setAllyCodeFormData({
        main_ally_code: formatAllyCode(user.main_ally_code || ''),
        alt_ally_code: formatAllyCode(user.alt_ally_code || ''),
        extra_ally_code: formatAllyCode(user.extra_ally_code || '')
      });
    } catch (err) {
      setAllyCodeError(err.response?.data?.error || 'Error updating ally codes');
    } finally {
      setAllyCodeLoading(false);
    }
  };

  return (
    <div className="settings-container">
      <h2>Settings</h2>
      
      <div className="settings-layout">
        <div className="settings-card ally-codes-card">
          <h3>Ally Codes</h3>
          <p className="current-info">Manage your SWGOH account ally codes</p>
          
          {allyCodeError && <div className="error-message">{allyCodeError}</div>}
          {allyCodeSuccess && <div className="success-message">{allyCodeSuccess}</div>}
          
          <form onSubmit={handleAllyCodeSubmit}>
            <div className="ally-codes-grid">
              <div className="form-group">
                <label>Main Account Ally Code</label>
                <input
                  type="text"
                  name="main_ally_code"
                  value={allyCodeFormData.main_ally_code}
                  onChange={handleAllyCodeChange}
                  placeholder="000-000-000"
                  maxLength="11"
                  className="input-field"
                  pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}"
                />
              </div>
              
              <div className="form-group">
                <label>Alt Account Ally Code</label>
                <input
                  type="text"
                  name="alt_ally_code"
                  value={allyCodeFormData.alt_ally_code}
                  onChange={handleAllyCodeChange}
                  placeholder="000-000-000"
                  maxLength="11"
                  className="input-field"
                  pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}"
                />
              </div>
              
              <div className="form-group">
                <label>Extra Ally Code</label>
                <input
                  type="text"
                  name="extra_ally_code"
                  value={allyCodeFormData.extra_ally_code}
                  onChange={handleAllyCodeChange}
                  placeholder="000-000-000"
                  maxLength="11"
                  className="input-field"
                  pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}"
                />
              </div>
            </div>
            
            <button type="submit" disabled={allyCodeLoading} className="btn-primary">
              {allyCodeLoading ? 'Saving...' : 'Save Ally Codes'}
            </button>
          </form>
        </div>

        <div className="settings-grid-bottom">
          <div className="settings-card">
            <h3>Change Username</h3>
            <p className="current-info">Current username: <strong>{currentUsername}</strong></p>
            
            {usernameError && <div className="error-message">{usernameError}</div>}
            {usernameSuccess && <div className="success-message">{usernameSuccess}</div>}
            
            <form onSubmit={handleUsernameSubmit}>
              <div className="form-group">
                <label>New Username</label>
                <input
                  type="text"
                  name="newUsername"
                  value={usernameFormData.newUsername}
                  onChange={handleUsernameChange}
                  required
                  placeholder="Enter your new username (min 3 characters)"
                  minLength="3"
                  maxLength="50"
                  className="input-field"
                />
              </div>
              
              <div className="form-group">
                <label>Password</label>
                <input
                  type="password"
                  name="password"
                  value={usernameFormData.password}
                  onChange={handleUsernameChange}
                  required
                  placeholder="Enter your password to confirm"
                  className="input-field"
                />
              </div>
              
              <button type="submit" disabled={usernameLoading} className="btn-primary">
                {usernameLoading ? 'Changing Username...' : 'Change Username'}
              </button>
            </form>
          </div>

          <div className="settings-card">
            <h3>Change Password</h3>
            
            {passwordError && <div className="error-message">{passwordError}</div>}
            {passwordSuccess && <div className="success-message">{passwordSuccess}</div>}
            
            <form onSubmit={handlePasswordSubmit}>
              <div className="form-group">
                <label>Current Password</label>
                <input
                  type="password"
                  name="currentPassword"
                  value={passwordFormData.currentPassword}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Enter your current password"
                  className="input-field"
                />
              </div>
              
              <div className="form-group">
                <label>New Password</label>
                <input
                  type="password"
                  name="newPassword"
                  value={passwordFormData.newPassword}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Enter your new password (min 6 characters)"
                  minLength="6"
                  className="input-field"
                />
              </div>
              
              <div className="form-group">
                <label>Confirm New Password</label>
                <input
                  type="password"
                  name="confirmPassword"
                  value={passwordFormData.confirmPassword}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Confirm your new password"
                  minLength="6"
                  className="input-field"
                />
              </div>
              
              <button type="submit" disabled={loading} className="btn-primary">
                {loading ? 'Changing Password...' : 'Change Password'}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Settings;

