import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './SWGOHData.css';

function SWGOHData() {
  const [units, setUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedUnit, setSelectedUnit] = useState(null);

  useEffect(() => {
    loadUnits();
  }, []);

  const loadUnits = async () => {
    try {
      setLoading(true);
      setError('');
      const response = await api.get('/swgoh/units');
      setUnits(response.data || []);
    } catch (err) {
      console.error('Error loading units:', err);
      setError(err.response?.data?.error || 'Error loading units from SWGOH.gg API');
    } finally {
      setLoading(false);
    }
  };

  const loadUnitDetails = async (unitId) => {
    try {
      const response = await api.get(`/swgoh/units/${unitId}`);
      setSelectedUnit(response.data);
    } catch (err) {
      console.error('Error loading unit details:', err);
      alert('Error loading unit details');
    }
  };

  const filteredUnits = units.filter(unit => {
    if (!unit) return false;
    const name = unit.name || unit.base_id || '';
    return name.toLowerCase().includes(searchTerm.toLowerCase());
  });

  if (loading) {
    return (
      <div className="swgoh-data-container">
        <div className="loading-spinner">Loading units from SWGOH.gg...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="swgoh-data-container">
        <h2>SWGOH.gg Units Database</h2>
        <div className="error-message">{error}</div>
        <button onClick={loadUnits} className="retry-button">Retry</button>
      </div>
    );
  }

  return (
    <div className="swgoh-data-container">
      <h2>SWGOH.gg Units Database</h2>
      <p className="subtitle">Browse all characters and units from SWGOH.gg</p>
      
      <div className="search-section">
        <input
          type="text"
          placeholder="Search units..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="search-input"
        />
        <div className="units-count">{filteredUnits.length} units found</div>
      </div>

      <div className="units-layout">
        <div className="units-list">
          {filteredUnits.length === 0 ? (
            <div className="no-units">No units found</div>
          ) : (
            filteredUnits.map((unit, index) => (
              <div
                key={unit.base_id || index}
                className="unit-item"
                onClick={() => loadUnitDetails(unit.base_id)}
              >
                <div className="unit-name">{unit.name || unit.base_id || 'Unknown'}</div>
                {unit.base_id && <div className="unit-id">{unit.base_id}</div>}
              </div>
            ))
          )}
        </div>

        {selectedUnit && (
          <div className="unit-details">
            <button onClick={() => setSelectedUnit(null)} className="close-button">×</button>
            <h3>{selectedUnit.name || selectedUnit.base_id}</h3>
            <div className="unit-info">
              <pre>{JSON.stringify(selectedUnit, null, 2)}</pre>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default SWGOHData;

