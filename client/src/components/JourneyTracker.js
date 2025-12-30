import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './Planner.css';

function JourneyTracker() {
  const [journeys, setJourneys] = useState([]);
  const [selectedJourney, setSelectedJourney] = useState(null);
  const [formData, setFormData] = useState({
    journey_name: '',
    character_name: '',
    current_stage: 0,
    total_stages: 0,
    unlocked: false,
    notes: ''
  });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadJourneys();
  }, []);

  const loadJourneys = async () => {
    try {
      const response = await api.get('/journey');
      setJourneys(response.data);
    } catch (error) {
      console.error('Error loading journeys:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (selectedJourney) {
        await api.put(`/journey/${selectedJourney.id}`, formData);
      } else {
        await api.post('/journey', formData);
      }
      resetForm();
      loadJourneys();
    } catch (error) {
      alert('Error saving journey: ' + (error.response?.data?.error || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this journey?')) return;

    try {
      await api.delete(`/journey/${id}`);
      loadJourneys();
      if (selectedJourney?.id === id) {
        resetForm();
      }
    } catch (error) {
      alert('Error deleting journey: ' + (error.response?.data?.error || error.message));
    }
  };

  const loadJourney = (journey) => {
    setSelectedJourney(journey);
    setFormData({
      journey_name: journey.journey_name,
      character_name: journey.character_name,
      current_stage: journey.current_stage,
      total_stages: journey.total_stages,
      unlocked: journey.unlocked,
      notes: journey.notes || ''
    });
  };

  const resetForm = () => {
    setSelectedJourney(null);
    setFormData({
      journey_name: '',
      character_name: '',
      current_stage: 0,
      total_stages: 0,
      unlocked: false,
      notes: ''
    });
  };

  const progress = (journey) => {
    if (journey.total_stages === 0) return 0;
    return Math.round((journey.current_stage / journey.total_stages) * 100);
  };

  return (
    <div className="planner-container">
      <h2>Journey Tracker</h2>
      
      <div className="planner-layout">
        <div className="planner-sidebar">
          <h3>Your Journeys</h3>
          <button onClick={resetForm} className="btn-primary">+ New Journey</button>
          <div className="plans-list">
            {journeys.map(journey => (
              <div key={journey.id} className="plan-item">
                <div onClick={() => loadJourney(journey)} className="plan-name">
                  <div>{journey.character_name}</div>
                  <div className="journey-meta">
                    <span>{journey.journey_name}</span>
                    <span>{journey.current_stage}/{journey.total_stages}</span>
                  </div>
                  <div className="progress-bar">
                    <div className="progress-fill" style={{ width: `${progress(journey)}%` }}></div>
                  </div>
                </div>
                <button onClick={() => handleDelete(journey.id)} className="btn-delete">Delete</button>
              </div>
            ))}
          </div>
        </div>

        <div className="planner-main">
          <form onSubmit={handleSubmit}>
            <div className="form-section">
              <label>Journey Name</label>
              <input
                type="text"
                value={formData.journey_name}
                onChange={(e) => setFormData({ ...formData, journey_name: e.target.value })}
                className="input-field"
                required
                placeholder="e.g., Commander Luke Skywalker"
              />
            </div>

            <div className="form-section">
              <label>Character Name</label>
              <input
                type="text"
                value={formData.character_name}
                onChange={(e) => setFormData({ ...formData, character_name: e.target.value })}
                className="input-field"
                required
                placeholder="e.g., CLS"
              />
            </div>

            <div className="form-row">
              <div className="form-section">
                <label>Current Stage</label>
                <input
                  type="number"
                  value={formData.current_stage}
                  onChange={(e) => setFormData({ ...formData, current_stage: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="0"
                />
              </div>

              <div className="form-section">
                <label>Total Stages</label>
                <input
                  type="number"
                  value={formData.total_stages}
                  onChange={(e) => setFormData({ ...formData, total_stages: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="1"
                  required
                />
              </div>
            </div>

            <div className="form-section">
              <label>
                <input
                  type="checkbox"
                  checked={formData.unlocked}
                  onChange={(e) => setFormData({ ...formData, unlocked: e.target.checked })}
                />
                Unlocked
              </label>
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
                {loading ? 'Saving...' : (selectedJourney ? 'Update Journey' : 'Create Journey')}
              </button>
              {selectedJourney && (
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

export default JourneyTracker;

