import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import CharacterModal from './CharacterModal';
import './Planner.css';

// GAC League Configuration
const GAC_LEAGUE_CONFIG = {
  kyber: {
    name: 'Kyber',
    '5v5': {
      maxSquadTeams: 11,
      maxFleetTeams: 3,
      territories: [
        { name: 'Territory 1', maxTeams: 3 },
        { name: 'Territory 2', maxTeams: 4 },
        { name: 'Territory 3', maxTeams: 3 },
        { name: 'Territory 4', maxTeams: 4 }
      ]
    },
    '3v3': {
      maxSquadTeams: 15,
      maxFleetTeams: 3,
      territories: [
        { name: 'Territory 1', maxTeams: 3 },
        { name: 'Territory 2', maxTeams: 5 },
        { name: 'Territory 3', maxTeams: 5 },
        { name: 'Territory 4', maxTeams: 5 }
      ]
    }
  },
  aurodium: {
    name: 'Aurodium',
    '5v5': {
      maxSquadTeams: 9,
      maxFleetTeams: 2,
      territories: [
        { name: 'Territory 1', maxTeams: 2 },
        { name: 'Territory 2', maxTeams: 3 },
        { name: 'Territory 3', maxTeams: 3 },
        { name: 'Territory 4', maxTeams: 3 }
      ]
    },
    '3v3': {
      maxSquadTeams: 13,
      maxFleetTeams: 2,
      territories: [
        { name: 'Territory 1', maxTeams: 2 },
        { name: 'Territory 2', maxTeams: 4 },
        { name: 'Territory 3', maxTeams: 5 },
        { name: 'Territory 4', maxTeams: 4 }
      ]
    }
  },
  chromium: {
    name: 'Chromium',
    '5v5': {
      maxSquadTeams: 7,
      maxFleetTeams: 2,
      territories: [
        { name: 'Territory 1', maxTeams: 2 },
        { name: 'Territory 2', maxTeams: 3 },
        { name: 'Territory 3', maxTeams: 2 },
        { name: 'Territory 4', maxTeams: 2 }
      ]
    },
    '3v3': {
      maxSquadTeams: 10,
      maxFleetTeams: 2,
      territories: [
        { name: 'Territory 1', maxTeams: 2 },
        { name: 'Territory 2', maxTeams: 3 },
        { name: 'Territory 3', maxTeams: 4 },
        { name: 'Territory 4', maxTeams: 3 }
      ]
    }
  },
  bronzium: {
    name: 'Bronzium',
    '5v5': {
      maxSquadTeams: 5,
      maxFleetTeams: 1,
      territories: [
        { name: 'Territory 1', maxTeams: 1 },
        { name: 'Territory 2', maxTeams: 2 },
        { name: 'Territory 3', maxTeams: 1 },
        { name: 'Territory 4', maxTeams: 2 }
      ]
    },
    '3v3': {
      maxSquadTeams: 7,
      maxFleetTeams: 1,
      territories: [
        { name: 'Territory 1', maxTeams: 1 },
        { name: 'Territory 2', maxTeams: 2 },
        { name: 'Territory 3', maxTeams: 3 },
        { name: 'Territory 4', maxTeams: 2 }
      ]
    }
  },
  carbonite: {
    name: 'Carbonite',
    '5v5': {
      maxSquadTeams: 3,
      maxFleetTeams: 1,
      territories: [
        { name: 'Territory 1', maxTeams: 1 },
        { name: 'Territory 2', maxTeams: 1 },
        { name: 'Territory 3', maxTeams: 1 },
        { name: 'Territory 4', maxTeams: 1 }
      ]
    },
    '3v3': {
      maxSquadTeams: 3,
      maxFleetTeams: 1,
      territories: [
        { name: 'Territory 1', maxTeams: 1 },
        { name: 'Territory 2', maxTeams: 1 },
        { name: 'Territory 3', maxTeams: 1 },
        { name: 'Territory 4', maxTeams: 1 }
      ]
    }
  }
};

function GACPlanner() {
  const [plans, setPlans] = useState([]);
  const [selectedPlan, setSelectedPlan] = useState(null);
  const [planName, setPlanName] = useState('');
  const [league, setLeague] = useState('kyber');
  const [format, setFormat] = useState('5v5');
  const [defenseTeams, setDefenseTeams] = useState([]);
  const [offenseTeams, setOffenseTeams] = useState([]);
  const [fleetTeams, setFleetTeams] = useState([]);
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(false);
  const [modalOpen, setModalOpen] = useState(false);
  const [modalTerritory, setModalTerritory] = useState(null); // { type: 'defense'|'offense', territoryIndex, slotIndex }

  useEffect(() => {
    loadPlans();
  }, []);

  const loadPlans = async () => {
    try {
      const response = await api.get('/gac');
      setPlans(response.data);
    } catch (error) {
      console.error('Error loading plans:', error);
    }
  };

  const handleCreatePlan = async () => {
    if (!planName.trim()) {
      alert('Please enter a plan name');
      return;
    }

    setLoading(true);
    try {
      await api.post('/gac', {
        plan_name: planName,
        league,
        format,
        defense_teams: defenseTeams,
        offense_teams: offenseTeams,
        fleet_teams: fleetTeams,
        notes
      });
      setPlanName('');
      setDefenseTeams([]);
      setOffenseTeams([]);
      setFleetTeams([]);
      setNotes('');
      setSelectedPlan(null);
      setLeague('kyber');
      setFormat('5v5');
      loadPlans();
    } catch (error) {
      alert('Error creating plan: ' + (error.response?.data?.error || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleUpdatePlan = async () => {
    if (!selectedPlan) return;

    setLoading(true);
    try {
      await api.put(`/gac/${selectedPlan.id}`, {
        plan_name: planName,
        league,
        format,
        defense_teams: defenseTeams,
        offense_teams: offenseTeams,
        fleet_teams: fleetTeams,
        notes
      });
      setSelectedPlan(null);
      setPlanName('');
      setDefenseTeams([]);
      setOffenseTeams([]);
      setFleetTeams([]);
      setNotes('');
      setLeague('kyber');
      setFormat('5v5');
      loadPlans();
    } catch (error) {
      alert('Error updating plan: ' + (error.response?.data?.error || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleDeletePlan = async (id) => {
    if (!window.confirm('Are you sure you want to delete this plan?')) return;

    try {
      await api.delete(`/gac/${id}`);
      loadPlans();
      if (selectedPlan?.id === id) {
        setSelectedPlan(null);
        setPlanName('');
        setDefenseTeams([]);
        setOffenseTeams([]);
        setFleetTeams([]);
        setNotes('');
        setLeague('kyber');
        setFormat('5v5');
      }
    } catch (error) {
      alert('Error deleting plan: ' + (error.response?.data?.error || error.message));
    }
  };

  const loadPlan = (plan) => {
    setSelectedPlan(plan);
    setPlanName(plan.plan_name);
    setLeague(plan.league || 'kyber');
    setFormat(plan.format || '5v5');
    const defense = typeof plan.defense_teams === 'string' ? JSON.parse(plan.defense_teams) : plan.defense_teams || [];
    const offense = typeof plan.offense_teams === 'string' ? JSON.parse(plan.offense_teams) : plan.offense_teams || [];
    const fleet = typeof plan.fleet_teams === 'string' ? JSON.parse(plan.fleet_teams) : plan.fleet_teams || [];
    setDefenseTeams(defense);
    setOffenseTeams(offense);
    setFleetTeams(fleet);
    setNotes(plan.notes || '');
  };

  const getLeagueConfig = () => {
    return GAC_LEAGUE_CONFIG[league]?.[format] || GAC_LEAGUE_CONFIG.kyber['5v5'];
  };

  const getTerritoryTeams = (territoryIndex, type = 'defense') => {
    const teams = type === 'defense' ? defenseTeams : offenseTeams;
    return teams.filter(team => team.territoryIndex === territoryIndex);
  };

  const addTeam = (type) => {
    if (type === 'fleet') {
      const config = getLeagueConfig();
      if (fleetTeams.length >= config.maxFleetTeams) {
        alert(`Maximum ${config.maxFleetTeams} fleet teams allowed for ${GAC_LEAGUE_CONFIG[league].name} ${format}`);
        return;
      }
      const newTeam = { name: '', characters: [] };
      setFleetTeams([...fleetTeams, newTeam]);
    }
  };

  const removeTeam = (type, index, territoryIndex = null) => {
    if (type === 'defense') {
      if (territoryIndex !== null) {
        const teamToRemove = defenseTeams.find((t, i) => 
          t.territoryIndex === territoryIndex && 
          defenseTeams.filter(tt => tt.territoryIndex === territoryIndex).indexOf(t) === index
        );
        if (teamToRemove) {
          setDefenseTeams(defenseTeams.filter(t => t !== teamToRemove));
        }
      } else {
        setDefenseTeams(defenseTeams.filter((_, i) => i !== index));
      }
    } else if (type === 'offense') {
      if (territoryIndex !== null) {
        const teamToRemove = offenseTeams.find((t, i) => 
          t.territoryIndex === territoryIndex && 
          offenseTeams.filter(tt => tt.territoryIndex === territoryIndex).indexOf(t) === index
        );
        if (teamToRemove) {
          setOffenseTeams(offenseTeams.filter(t => t !== teamToRemove));
        }
      } else {
        setOffenseTeams(offenseTeams.filter((_, i) => i !== index));
      }
    } else if (type === 'fleet') {
      setFleetTeams(fleetTeams.filter((_, i) => i !== index));
    }
  };

  const config = getLeagueConfig();
  const totalDefenseTeams = defenseTeams.length;

  return (
    <div className="planner-container">
      <h2>GAC Planner</h2>
      
      <div className="planner-main">
        <div className="form-section">
          <input
            type="text"
            placeholder="Plan Name"
            value={planName}
            onChange={(e) => setPlanName(e.target.value)}
            className="input-field"
          />
        </div>

        <div className="form-section">
          <textarea
            placeholder="Notes"
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            className="textarea-field"
            rows="4"
          />
        </div>

        <div className="gac-league-selector">
          <div className="selector-group">
            <label>League:</label>
            <select value={league} onChange={(e) => { setLeague(e.target.value); setDefenseTeams([]); setOffenseTeams([]); setFleetTeams([]); }} className="input-field">
              <option value="kyber">Kyber</option>
              <option value="aurodium">Aurodium</option>
              <option value="chromium">Chromium</option>
              <option value="bronzium">Bronzium</option>
              <option value="carbonite">Carbonite</option>
            </select>
          </div>
          <div className="selector-group">
            <label>Format:</label>
            <select value={format} onChange={(e) => { setFormat(e.target.value); setDefenseTeams([]); setOffenseTeams([]); setFleetTeams([]); }} className="input-field">
              <option value="5v5">5v5</option>
              <option value="3v3">3v3</option>
            </select>
          </div>
          <div className="league-info">
            <span>Max Squad Teams: <strong>{config.maxSquadTeams}</strong></span>
            <span>Max Fleet Teams: <strong>{config.maxFleetTeams}</strong></span>
          </div>
        </div>

        <div className="gac-main-layout">
          <div className="gac-defense-section">
            <div className="side-header">
              <h3>🛡️ Defense Teams by Territory</h3>
              <div className="defense-summary">
                Total: {totalDefenseTeams}/{config.maxSquadTeams}
              </div>
            </div>
            <div className="territories-grid">
              {config.territories.map((territory, territoryIndex) => {
                const territoryTeams = getTerritoryTeams(territoryIndex, 'defense');
                
                return (
                  <div key={territoryIndex} className="territory-card">
                    <div className="territory-header">
                      <h4>{territory.name}</h4>
                      <span className="territory-limit">{territoryTeams.length}/{territory.maxTeams}</span>
                    </div>
                    <div className="territory-teams">
                      {Array.from({ length: territory.maxTeams }, (_, slotIndex) => {
                        const actualTeam = territoryTeams[slotIndex];
                        const hasData = actualTeam && (actualTeam.name || (Array.isArray(actualTeam.characters) && actualTeam.characters.length > 0) || (typeof actualTeam.characters === 'string' && actualTeam.characters.trim()));
                        
                        const teamCharacters = actualTeam?.characters || [];
                        const characterNames = Array.isArray(teamCharacters) 
                          ? teamCharacters.map(c => typeof c === 'string' ? c : c.name).filter(Boolean)
                          : [];
                        
                        return (
                          <div key={`defense-${territoryIndex}-${slotIndex}`} className={`team-card defense-card ${!hasData ? 'empty-slot' : ''}`}>
                            <button
                              onClick={() => {
                                setModalTerritory({ type: 'defense', territoryIndex, slotIndex });
                                setModalOpen(true);
                              }}
                              className="team-select-button"
                            >
                              {characterNames.length > 0 ? (
                                <div className="team-characters-display">
                                  <div className="team-characters-count">{characterNames.length} characters</div>
                                  <div className="team-characters-list">
                                    {characterNames.slice(0, 3).join(', ')}
                                    {characterNames.length > 3 && ` +${characterNames.length - 3} more`}
                                  </div>
                                </div>
                              ) : (
                                <div className="team-select-placeholder">
                                  Click to select characters
                                </div>
                              )}
                            </button>
                            {hasData && actualTeam && (
                              <button 
                                onClick={() => {
                                  const teamToRemove = defenseTeams.find(t => 
                                    t.territoryIndex === territoryIndex && 
                                    defenseTeams.filter(tt => tt.territoryIndex === territoryIndex).indexOf(t) === slotIndex
                                  );
                                  if (teamToRemove) {
                                    setDefenseTeams(defenseTeams.filter(t => t !== teamToRemove));
                                  }
                                }} 
                                className="btn-delete-small team-delete-btn"
                              >
                                Clear Team
                              </button>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="gac-offense-section">
            <div className="side-header">
              <h3>⚔️ Offense Teams by Territory</h3>
              <div className="offense-summary">
                Total: {offenseTeams.length}/{config.maxSquadTeams}
              </div>
            </div>
            <div className="territories-grid">
              {config.territories.map((territory, territoryIndex) => {
                const territoryTeams = getTerritoryTeams(territoryIndex, 'offense');
                
                return (
                  <div key={territoryIndex} className="territory-card">
                    <div className="territory-header">
                      <h4>{territory.name}</h4>
                      <span className="territory-limit">{territoryTeams.length}/{territory.maxTeams}</span>
                    </div>
                    <div className="territory-teams">
                      {Array.from({ length: territory.maxTeams }, (_, slotIndex) => {
                        const actualTeam = territoryTeams[slotIndex];
                        const hasData = actualTeam && (actualTeam.name || (Array.isArray(actualTeam.characters) && actualTeam.characters.length > 0) || (typeof actualTeam.characters === 'string' && actualTeam.characters.trim()));
                        
                        const teamCharacters = actualTeam?.characters || [];
                        const characterNames = Array.isArray(teamCharacters) 
                          ? teamCharacters.map(c => typeof c === 'string' ? c : c.name).filter(Boolean)
                          : [];
                        
                        return (
                          <div key={`offense-${territoryIndex}-${slotIndex}`} className={`team-card offense-card ${!hasData ? 'empty-slot' : ''}`}>
                            <button
                              onClick={() => {
                                setModalTerritory({ type: 'offense', territoryIndex, slotIndex });
                                setModalOpen(true);
                              }}
                              className="team-select-button"
                            >
                              {characterNames.length > 0 ? (
                                <div className="team-characters-display">
                                  <div className="team-characters-count">{characterNames.length} characters</div>
                                  <div className="team-characters-list">
                                    {characterNames.slice(0, 3).join(', ')}
                                    {characterNames.length > 3 && ` +${characterNames.length - 3} more`}
                                  </div>
                                </div>
                              ) : (
                                <div className="team-select-placeholder">
                                  Click to select characters
                                </div>
                              )}
                            </button>
                            {hasData && actualTeam && (
                              <button 
                                onClick={() => {
                                  const teamToRemove = offenseTeams.find(t => 
                                    t.territoryIndex === territoryIndex && 
                                    offenseTeams.filter(tt => tt.territoryIndex === territoryIndex).indexOf(t) === slotIndex
                                  );
                                  if (teamToRemove) {
                                    setOffenseTeams(offenseTeams.filter(t => t !== teamToRemove));
                                  }
                                }} 
                                className="btn-delete-small team-delete-btn"
                              >
                                Clear Team
                              </button>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        <div className="form-actions">
          {selectedPlan ? (
            <button onClick={handleUpdatePlan} disabled={loading} className="btn-primary">
              {loading ? 'Updating...' : 'Update Plan'}
            </button>
          ) : (
            <button onClick={handleCreatePlan} disabled={loading} className="btn-primary">
              {loading ? 'Creating...' : 'Create Plan'}
            </button>
          )}
        </div>
      </div>

      <CharacterModal
        isOpen={modalOpen}
        onClose={() => {
          setModalOpen(false);
          setModalTerritory(null);
        }}
        onSelect={(selectedChars) => {
          if (!modalTerritory) return;
          
          const { type, territoryIndex, slotIndex } = modalTerritory;
          const teams = type === 'defense' ? defenseTeams : offenseTeams;
          const setTeams = type === 'defense' ? setDefenseTeams : setOffenseTeams;
          
          const updated = [...teams];
          const existingTeam = updated.find(t => 
            t.territoryIndex === territoryIndex && 
            teams.filter(tt => tt.territoryIndex === territoryIndex).indexOf(t) === slotIndex
          );
          
          if (existingTeam) {
            const idx = updated.indexOf(existingTeam);
            updated[idx] = { ...updated[idx], characters: selectedChars };
          } else {
            const newTeam = { name: '', characters: selectedChars, territoryIndex };
            updated.push(newTeam);
          }
          setTeams(updated);
        }}
        selectedCharacters={(() => {
          if (!modalTerritory) return [];
          const { type, territoryIndex, slotIndex } = modalTerritory;
          const teams = type === 'defense' ? defenseTeams : offenseTeams;
          const territoryTeams = teams.filter(t => t.territoryIndex === territoryIndex);
          const actualTeam = territoryTeams[slotIndex];
          if (!actualTeam || !actualTeam.characters) return [];
          return Array.isArray(actualTeam.characters) 
            ? actualTeam.characters.filter(c => c && (typeof c === 'object' ? c.base_id : true))
            : [];
        })()}
      />
    </div>
  );
}

export default GACPlanner;
