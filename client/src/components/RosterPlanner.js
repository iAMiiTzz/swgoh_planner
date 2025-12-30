import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './Planner.css';

function RosterPlanner() {
  const [characters, setCharacters] = useState([]);
  const [selectedCharacter, setSelectedCharacter] = useState(null);
  const [formData, setFormData] = useState({
    character_name: '',
    star_level: 1,
    gear_level: 1,
    relic_level: 0,
    zeta_count: 0,
    omicron_count: 0,
    notes: ''
  });
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    loadCharacters();
  }, []);

  const loadCharacters = async () => {
    try {
      const response = await api.get('/roster');
      setCharacters(response.data);
    } catch (error) {
      console.error('Error loading characters:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (selectedCharacter) {
        await api.put(`/roster/${selectedCharacter.id}`, formData);
      } else {
        await api.post('/roster', formData);
      }
      resetForm();
      loadCharacters();
    } catch (error) {
      alert('Error saving character: ' + (error.response?.data?.error || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this character?')) return;

    try {
      await api.delete(`/roster/${id}`);
      loadCharacters();
      if (selectedCharacter?.id === id) {
        resetForm();
      }
    } catch (error) {
      alert('Error deleting character: ' + (error.response?.data?.error || error.message));
    }
  };

  const loadCharacter = (character) => {
    setSelectedCharacter(character);
    setFormData({
      character_name: character.character_name,
      star_level: character.star_level,
      gear_level: character.gear_level,
      relic_level: character.relic_level,
      zeta_count: character.zeta_count,
      omicron_count: character.omicron_count,
      notes: character.notes || ''
    });
  };

  const resetForm = () => {
    setSelectedCharacter(null);
    setFormData({
      character_name: '',
      star_level: 1,
      gear_level: 1,
      relic_level: 0,
      zeta_count: 0,
      omicron_count: 0,
      notes: ''
    });
  };

  const filteredCharacters = characters.filter(char =>
    char.character_name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="planner-container">
      <h2>Roster Planner</h2>
      
      <div className="planner-layout">
        <div className="planner-sidebar">
          <h3>Your Roster</h3>
          <input
            type="text"
            placeholder="Search characters..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="input-field"
            style={{ marginBottom: '15px' }}
          />
          <button onClick={resetForm} className="btn-primary">+ New Character</button>
          <div className="plans-list">
            {filteredCharacters.map(character => (
              <div key={character.id} className="plan-item">
                <div onClick={() => loadCharacter(character)} className="plan-name">
                  <div>{character.character_name}</div>
                  <div className="character-meta">
                    <span>⭐ {character.star_level}/7</span>
                    <span>⚙️ G{character.gear_level}</span>
                    {character.relic_level > 0 && <span>💎 R{character.relic_level}</span>}
                  </div>
                </div>
                <button onClick={() => handleDelete(character.id)} className="btn-delete">Delete</button>
              </div>
            ))}
          </div>
        </div>

        <div className="planner-main">
          <form onSubmit={handleSubmit}>
            <div className="form-section">
              <label>Character Name</label>
              <input
                type="text"
                value={formData.character_name}
                onChange={(e) => setFormData({ ...formData, character_name: e.target.value })}
                className="input-field"
                required
                placeholder="e.g., Darth Vader"
              />
            </div>

            <div className="form-row">
              <div className="form-section">
                <label>Star Level (1-7)</label>
                <input
                  type="number"
                  value={formData.star_level}
                  onChange={(e) => setFormData({ ...formData, star_level: parseInt(e.target.value) || 1 })}
                  className="input-field"
                  min="1"
                  max="7"
                  required
                />
              </div>

              <div className="form-section">
                <label>Gear Level (1-13)</label>
                <input
                  type="number"
                  value={formData.gear_level}
                  onChange={(e) => setFormData({ ...formData, gear_level: parseInt(e.target.value) || 1 })}
                  className="input-field"
                  min="1"
                  max="13"
                  required
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-section">
                <label>Relic Level (0-9)</label>
                <input
                  type="number"
                  value={formData.relic_level}
                  onChange={(e) => setFormData({ ...formData, relic_level: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="0"
                  max="9"
                />
              </div>

              <div className="form-section">
                <label>Zeta Count</label>
                <input
                  type="number"
                  value={formData.zeta_count}
                  onChange={(e) => setFormData({ ...formData, zeta_count: parseInt(e.target.value) || 0 })}
                  className="input-field"
                  min="0"
                />
              </div>
            </div>

            <div className="form-section">
              <label>Omicron Count</label>
              <input
                type="number"
                value={formData.omicron_count}
                onChange={(e) => setFormData({ ...formData, omicron_count: parseInt(e.target.value) || 0 })}
                className="input-field"
                min="0"
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
                {loading ? 'Saving...' : (selectedCharacter ? 'Update Character' : 'Add Character')}
              </button>
              {selectedCharacter && (
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

export default RosterPlanner;

