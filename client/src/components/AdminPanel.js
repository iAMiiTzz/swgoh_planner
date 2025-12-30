import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './AdminPanel.css';

function AdminPanel() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showAddForm, setShowAddForm] = useState(false);
  const [formData, setFormData] = useState({
    username: '',
    password: '',
    role: 'user'
  });

  useEffect(() => {
    loadUsers();
  }, []);

  const loadUsers = async () => {
    try {
      const response = await api.get('/admin/users');
      setUsers(response.data);
    } catch (error) {
      console.error('Error loading users:', error);
      setError('Error loading users. Make sure you have admin access.');
    }
  };

  const handleAddUser = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      await api.post('/admin/users', formData);
      setSuccess('User created successfully!');
      setFormData({ username: '', password: '', role: 'user' });
      setShowAddForm(false);
      loadUsers();
    } catch (err) {
      const errorMessage = err.response?.data?.error || err.message || 'Error creating user';
      setError(errorMessage);
      console.error('Error creating user:', err.response?.data || err);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
      return;
    }

    try {
      await api.delete(`/admin/users/${userId}`);
      setSuccess('User deleted successfully!');
      loadUsers();
    } catch (err) {
      setError(err.response?.data?.error || 'Error deleting user');
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const formatAllyCode = (code) => {
    if (!code) return '-';
    const cleaned = code.replace(/-/g, '');
    if (cleaned.length === 9) {
      return `${cleaned.slice(0, 3)}-${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
    }
    return code;
  };

  const getAllAllyCodes = (user) => {
    const codes = [];
    if (user.main_ally_code) codes.push(`Main: ${formatAllyCode(user.main_ally_code)}`);
    if (user.alt_ally_code) codes.push(`Alt: ${formatAllyCode(user.alt_ally_code)}`);
    if (user.extra_ally_code) codes.push(`Extra: ${formatAllyCode(user.extra_ally_code)}`);
    return codes.length > 0 ? codes.join(', ') : 'None';
  };

  return (
    <div className="admin-panel-container">
      <div className="admin-header">
        <h2>User Management</h2>
        <button onClick={() => setShowAddForm(!showAddForm)} className="btn-primary">
          {showAddForm ? 'Cancel' : '+ Add User'}
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}

      {showAddForm && (
        <div className="add-user-form">
          <h3>Add New User</h3>
          <form onSubmit={handleAddUser}>
            <div className="form-row">
              <div className="form-group">
                <label>Username</label>
                <input
                  type="text"
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                  required
                  className="input-field"
                  placeholder="Enter username"
                />
              </div>
              <div className="form-group">
                <label>Password</label>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  required
                  minLength="6"
                  className="input-field"
                  placeholder="Enter password (min 6 characters)"
                />
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Role</label>
                <select
                  value={formData.role}
                  onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                  className="input-field"
                >
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
            <button type="submit" disabled={loading} className="btn-primary">
              {loading ? 'Creating...' : 'Create User'}
            </button>
          </form>
        </div>
      )}

      <div className="users-table">
        <h3>All Users ({users.length})</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Role</th>
              <th>Ally Codes</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map(user => (
              <tr key={user.id}>
                <td>{user.id}</td>
                <td>{user.username}</td>
                <td>
                  <span className={`role-badge ${user.role === 'admin' ? 'admin' : 'user'}`}>
                    {user.role || 'user'}
                  </span>
                </td>
                <td className="ally-codes-cell">
                  <div className="ally-codes-display">
                    {user.main_ally_code && (
                      <span className="ally-code-item">
                        <strong>Main:</strong> {formatAllyCode(user.main_ally_code)}
                      </span>
                    )}
                    {user.alt_ally_code && (
                      <span className="ally-code-item">
                        <strong>Alt:</strong> {formatAllyCode(user.alt_ally_code)}
                      </span>
                    )}
                    {user.extra_ally_code && (
                      <span className="ally-code-item">
                        <strong>Extra:</strong> {formatAllyCode(user.extra_ally_code)}
                      </span>
                    )}
                    {!user.main_ally_code && !user.alt_ally_code && !user.extra_ally_code && (
                      <span className="no-ally-codes">None</span>
                    )}
                  </div>
                </td>
                <td>{formatDate(user.created_at)}</td>
                <td>
                  <button
                    onClick={() => handleDeleteUser(user.id)}
                    className="btn-delete"
                    disabled={user.role === 'admin'}
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {users.length === 0 && (
          <p className="no-users">No users found.</p>
        )}
      </div>
    </div>
  );
}

export default AdminPanel;

