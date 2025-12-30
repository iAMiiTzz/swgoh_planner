import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './CharacterModal.css';

function CharacterModal({ isOpen, onClose, onSelect, selectedCharacters = [] }) {
  const [characters, setCharacters] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selected, setSelected] = useState(selectedCharacters);

  useEffect(() => {
    if (isOpen && characters.length === 0) {
      loadCharacters();
    }
  }, [isOpen]);

  useEffect(() => {
    setSelected(selectedCharacters);
  }, [selectedCharacters]);

  const loadCharacters = async () => {
    try {
      setLoading(true);
      const response = await api.get('/swgoh/units');
      // Filter to get only characters (units with base_id)
      const chars = (response.data || []).filter(unit => unit.base_id && unit.name);
      setCharacters(chars);
    } catch (error) {
      console.error('Error loading characters:', error);
      alert('Error loading characters from SWGOH.gg');
    } finally {
      setLoading(false);
    }
  };

  const filteredCharacters = characters.filter(char => 
    char.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    char.base_id?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const toggleCharacter = (character) => {
    const isSelected = selected.some(c => c.base_id === character.base_id);
    if (isSelected) {
      setSelected(selected.filter(c => c.base_id !== character.base_id));
    } else {
      setSelected([...selected, { name: character.name, base_id: character.base_id }]);
    }
  };

  const handleConfirm = () => {
    onSelect(selected);
    onClose();
  };

  const handleClear = () => {
    setSelected([]);
  };

  if (!isOpen) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Select Characters</h2>
          <button className="modal-close" onClick={onClose}>×</button>
        </div>
        
        <div className="modal-search">
          <input
            type="text"
            placeholder="Search characters..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="modal-search-input"
          />
          <div className="selected-count">
            {selected.length} selected
          </div>
        </div>

        {loading ? (
          <div className="modal-loading">Loading characters...</div>
        ) : (
          <>
            <div className="modal-characters">
              {filteredCharacters.map((character) => {
                const isSelected = selected.some(c => c.base_id === character.base_id);
                return (
                  <div
                    key={character.base_id}
                    className={`character-item ${isSelected ? 'selected' : ''}`}
                    onClick={() => toggleCharacter(character)}
                  >
                    <div className="character-checkbox">
                      {isSelected && '✓'}
                    </div>
                    <div className="character-name">{character.name || character.base_id}</div>
                  </div>
                );
              })}
            </div>

            <div className="modal-footer">
              <button onClick={handleClear} className="btn-clear">Clear All</button>
              <div className="modal-actions">
                <button onClick={onClose} className="btn-cancel">Cancel</button>
                <button onClick={handleConfirm} className="btn-confirm">Confirm ({selected.length})</button>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}

export default CharacterModal;

