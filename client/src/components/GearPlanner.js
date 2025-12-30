import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './Planner.css';

function GearPlanner() {
  const [items, setItems] = useState([]);
  const [selectedItem, setSelectedItem] = useState(null);
  const [formData, setFormData] = useState({
    character_name: '',
    gear_name: '',
    gear_type: 'gear',
    target_quantity: 0,
    current_quantity: 0,
    priority: 5,
    farming_location: '',
    notes: ''
  });
  const [loading, setLoading] = useState(false);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    loadItems();
  }, []);

  const loadItems = async () => {
    try {
      const response = await api.get('/gear');
      setItems(response.data);
    } catch (error) {
      console.error('Error loading gear items:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (selectedItem) {
        await api.put(`/gear/${selectedItem.id}`, formData);
      } else {
        await api.post('/gear', formData);
      }
      resetForm();
      loadItems();
    } catch (error) {
      alert('Error saving gear item: ' + (error.response?.data?.error || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this item?')) return;

    try {
      await api.delete(`/gear/${id}`);
      loadItems();
      if (selectedItem?.id === id) {
        resetForm();
      }
    } catch (error) {
      alert('Error deleting item: ' + (error.response?.data?.error || error.message));
    }
  };

  const loadItem = (item) => {
    setSelectedItem(item);
    setFormData({
      character_name: item.character_name || '',
      gear_name: item.gear_name,
      gear_type: item.gear_type,
      target_quantity: item.target_quantity,
      current_quantity: item.current_quantity,
      priority: item.priority,
      farming_location: item.farming_location || '',
      notes: item.notes || ''
    });
  };

  const resetForm = () => {
    setSelectedItem(null);
    setFormData({
      character_name: '',
      gear_name: '',
      gear_type: 'gear',
      target_quantity: 0,
      current_quantity: 0,
      priority: 5,
      farming_location: '',
      notes: ''
    });
  };

  const progress = (item) => {
    if (item.target_quantity === 0) return 0;
    return Math.min(100, Math.round((item.current_quantity / item.target_quantity) * 100));
  };

  const filteredItems = filter === 'all'
    ? items
    : items.filter(item => item.gear_type === filter);

  const sortedItems = [...filteredItems].sort((a, b) => b.priority - a.priority);

  return (
    <div className="planner-container">
      <h2>Gear/Relic Planner</h2>
      
      <div className="planner-layout">
        <div className="planner-sidebar">
          <h3>Farming List</h3>
          <div style={{ marginBottom: '15px' }}>
            <select
              value={filter}
              onChange={(e) => setFilter(e.target.value)}
              className="input-field"
            >
              <option value="all">All Items</option>
              <option value="gear">Gear Only</option>
              <option value="relic">Relic Only</option>
            </select>
          </div>
          <button onClick={resetForm} className="btn-primary">+ New Item</button>
          <div className="plans-list">
            {sortedItems.map(item => (
              <div key={item.id} className="plan-item">
                <div onClick={() => loadItem(item)} className="plan-name">
                  <div>{item.gear_name}</div>
                  {item.character_name && <div className="gear-meta">{item.character_name}</div>}
                  <div className="gear-meta">
                    <span>{item.current_quantity}/{item.target_quantity}</span>
                    <span>Priority: {item.priority}</span>
                  </div>
                  <div className="progress-bar">
                    <div className="progress-fill" style={{ width: `${progress(item)}%` }}></div>
                  </div>
                </div>
                <button onClick={() => handleDelete(item.id)} className="btn-delete">Delete</button>
              </div>
            ))}
          </div>
        </div>

        <div className="planner-main">
          <form onSubmit={handleSubmit}>
            <div className="form-section">
              <label>Character Name (Optional)</label>
              <input
                type="text"
                value={formData.character_name}
                onChange={(e) => setFormData({ ...formData, character_name: e.target.value })}
                className="input-field"
                placeholder="e.g., Darth Vader"
              />
            </div>

            <div className="form-section">
              <label>Gear/Relic Name</label>
              <input
                type="text"
                value={formData.gear_name}
                onChange={(e) => setFormData({ ...formData, gear_name: e.target.value })}
                className="input-field"
                required
                placeholder="e.g., Kyrotech Shock Prod"
              />
            </div>

            <div className="form-section">
              <label>Type</label>
              <select
                value={formData.gear_type}
                onChange={(e) => setFormData({ ...formData, gear_type: e.target.value })}
                className="input-field"
              >
                <option value="gear">Gear</option>
                <option value="relic">Relic</option>
              </select>
            </div>

            <div className="form-row">
              <div className="form-section">
                <label>Current Quantity</label>
                <input
                  type="number"
                  value={formData.current_quantity}
                  onChange={(e) => setFormData({ ...formData, current_quantity: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="0"
                  required
                />
              </div>

              <div className="form-section">
                <label>Target Quantity</label>
                <input
                  type="number"
                  value={formData.target_quantity}
                  onChange={(e) => setFormData({ ...formData, target_quantity: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="1"
                  required
                />
              </div>
            </div>

            <div className="form-section">
              <label>Priority (1-10, 10 = highest)</label>
              <input
                type="number"
                value={formData.priority}
                onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) || 5 })}
                className="input-field"
                min="1"
                max="10"
                required
              />
            </div>

            <div className="form-section">
              <label>Farming Location</label>
              <input
                type="text"
                value={formData.farming_location}
                onChange={(e) => setFormData({ ...formData, farming_location: e.target.value })}
                className="input-field"
                placeholder="e.g., Light Side 1-A, Cantina 2-B"
              />
            </div>

            <div className="form-section">
              <label>Notes</label>
              <textarea
                value={formData.notes}
                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                className="textarea-field"
                rows="4"
                placeholder="Additional notes..."
              />
            </div>

            <div className="form-actions">
              <button type="submit" disabled={loading} className="btn-primary">
                {loading ? 'Saving...' : (selectedItem ? 'Update Item' : 'Add Item')}
              </button>
              {selectedItem && (
                <button type="button" onClick={resetForm} className="btn-secondary">
                  Cancel
                </button>
              )}
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

export default GearPlanner;

