<?php
require_once 'config/auth.php';
requireAuth();
require_once 'includes/header.php';
?>
<div class="gac-container">
    <h2>GAC Planner</h2>
    
    <div class="gac-controls">
        <div class="form-group" style="display: inline-block; margin-right: 20px; width: 200px;">
            <label>Plan Name</label>
            <input type="text" id="planName" placeholder="Enter plan name" style="width: 100%;">
        </div>
        <div class="form-group" style="display: inline-block; margin-right: 20px; width: 150px;">
            <label>League</label>
            <select id="league" style="width: 100%;">
                <option value="kyber">Kyber</option>
                <option value="aurodium">Aurodium</option>
                <option value="chromium">Chromium</option>
                <option value="bronzium">Bronzium</option>
                <option value="carbonite">Carbonite</option>
            </select>
        </div>
        <div class="form-group" style="display: inline-block; margin-right: 20px; width: 120px;">
            <label>Format</label>
            <select id="format" style="width: 100%;">
                <option value="5v5">5v5</option>
                <option value="3v3">3v3</option>
            </select>
        </div>
        <button onclick="loadPlan()" class="btn-secondary" style="margin-right: 10px;">Load Plan</button>
        <button onclick="savePlan()" class="btn-primary">Save Plan</button>
    </div>
    
    <div id="planInfo" class="card" style="margin-top: 20px; margin-bottom: 20px;">
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div><strong>Max Squad Teams:</strong> <span id="maxSquadTeams">-</span></div>
            <div><strong>Max Fleet Teams:</strong> <span id="maxFleetTeams">-</span></div>
            <div><strong>Squad Teams Used:</strong> <span id="squadTeamsUsed">0</span></div>
            <div><strong>Fleet Teams Used:</strong> <span id="fleetTeamsUsed">0</span></div>
        </div>
    </div>
    
    <div class="gac-layout">
        <!-- Defense Section -->
        <div class="gac-section">
            <h3>Defense</h3>
            <div id="defenseTerritories" class="territories-grid">
                <!-- Territories will be generated here -->
            </div>
        </div>
        
        <!-- Offense Section -->
        <div class="gac-section">
            <h3>Offense</h3>
            <div id="offenseTeams" class="offense-teams">
                <!-- Offense teams will be generated here -->
            </div>
        </div>
        
        <!-- Fleet Section -->
        <div class="gac-section">
            <h3>Fleet</h3>
            <div id="fleetTeams" class="fleet-teams">
                <!-- Fleet teams will be generated here -->
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h3>Notes</h3>
        <textarea id="notes" rows="4" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 1rem;" placeholder="Add notes about this plan..."></textarea>
    </div>
</div>

<!-- Character Selection Modal -->
<div id="characterModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 900px; max-height: 90vh;">
        <div class="modal-header">
            <h3>Select Characters</h3>
            <button class="modal-close" onclick="closeCharacterModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 15px;">
                <input type="text" id="characterSearch" placeholder="Search characters..." style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 6px;">
            </div>
            <div id="characterGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; max-height: 500px; overflow-y: auto; padding: 10px;">
                <!-- Characters will be loaded here -->
            </div>
            <div id="selectedCharacters" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <div style="font-weight: 600; margin-bottom: 10px;">Selected Characters:</div>
                <div id="selectedList" style="display: flex; flex-wrap: wrap; gap: 8px; min-height: 60px;">
                    <!-- Selected characters will appear here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeCharacterModal()" class="btn-secondary">Cancel</button>
                <button type="button" onclick="confirmCharacterSelection()" class="btn-primary">Confirm Selection</button>
            </div>
        </div>
    </div>
</div>

<script>
// GAC League Configuration based on the table
const GAC_CONFIG = {
    kyber: {
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

let currentPlan = {
    id: null,
    plan_name: '',
    league: 'kyber',
    format: '5v5',
    defense_teams: [],
    offense_teams: [],
    fleet_teams: [],
    notes: ''
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('league').addEventListener('change', updateLayout);
    document.getElementById('format').addEventListener('change', updateLayout);
    updateLayout();
});

function updateLayout() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Update info display
    document.getElementById('maxSquadTeams').textContent = config.maxSquadTeams;
    document.getElementById('maxFleetTeams').textContent = config.maxFleetTeams;
    
    // Generate defense territories
    generateDefenseTerritories(config.territories);
    
    // Generate offense teams
    generateOffenseTeams(config.maxSquadTeams);
    
    // Generate fleet teams
    generateFleetTeams(config.maxFleetTeams);
    
    updateCounts();
}

function generateDefenseTerritories(territories) {
    const container = document.getElementById('defenseTerritories');
    container.innerHTML = '';
    
    territories.forEach((territory, index) => {
        const territoryDiv = document.createElement('div');
        territoryDiv.className = 'territory-card';
        territoryDiv.innerHTML = `
            <h4>${territory.name}</h4>
            <div class="territory-teams" data-territory="${index}">
                ${Array(territory.maxTeams).fill(0).map((_, i) => `
                    <div class="team-slot" data-territory="${index}" data-slot="${i}">
                        <button type="button" class="team-select-button" onclick="openCharacterModal('defense', ${index}, ${i})">
                            <div class="team-characters-display" id="defense-${index}-${i}">
                                <span class="team-select-placeholder">Select Team ${i + 1}</span>
                            </div>
                        </button>
                    </div>
                `).join('')}
            </div>
        `;
        container.appendChild(territoryDiv);
    });
}

function generateOffenseTeams(maxTeams) {
    const container = document.getElementById('offenseTeams');
    container.innerHTML = '';
    
    for (let i = 0; i < maxTeams; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-slot';
        teamDiv.innerHTML = `
            <button type="button" class="team-select-button" onclick="openCharacterModal('offense', ${i})">
                <div class="team-characters-display" id="offense-${i}">
                    <span class="team-select-placeholder">Select Offense Team ${i + 1}</span>
                </div>
            </button>
        `;
        container.appendChild(teamDiv);
    }
}

function generateFleetTeams(maxTeams) {
    const container = document.getElementById('fleetTeams');
    container.innerHTML = '';
    
    for (let i = 0; i < maxTeams; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-slot';
        teamDiv.innerHTML = `
            <button type="button" class="team-select-button" onclick="openCharacterModal('fleet', ${i})">
                <div class="team-characters-display" id="fleet-${i}">
                    <span class="team-select-placeholder">Select Fleet Team ${i + 1}</span>
                </div>
            </button>
        `;
        container.appendChild(teamDiv);
    }
}

function updateCounts() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Count defense teams (teams with characters)
    let defenseCount = 0;
    document.querySelectorAll('#defenseTerritories .team-characters-display').forEach(display => {
        if (display.querySelector('.character-image')) defenseCount++;
    });
    
    // Count offense teams
    let offenseCount = 0;
    document.querySelectorAll('#offenseTeams .team-characters-display').forEach(display => {
        if (display.querySelector('.character-image')) offenseCount++;
    });
    
    // Count fleet teams
    let fleetCount = 0;
    document.querySelectorAll('#fleetTeams .team-characters-display').forEach(display => {
        if (display.querySelector('.character-image')) fleetCount++;
    });
    
    document.getElementById('squadTeamsUsed').textContent = defenseCount + offenseCount;
    document.getElementById('fleetTeamsUsed').textContent = fleetCount;
}

function collectPlanData() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Collect defense teams by territory
    const defenseTeams = [];
    config.territories.forEach((territory, tIndex) => {
        const teams = [];
        for (let i = 0; i < territory.maxTeams; i++) {
            const display = document.getElementById(`defense-${tIndex}-${i}`);
            if (display) {
                const characters = Array.from(display.querySelectorAll('.character-image')).map(img => ({
                    id: img.dataset.characterId,
                    name: img.dataset.characterName,
                    image: img.src
                }));
                if (characters.length > 0) {
                    teams.push(characters);
                }
            }
        }
        defenseTeams.push({
            territory: territory.name,
            teams: teams
        });
    });
    
    // Collect offense teams
    const offenseTeams = [];
    for (let i = 0; i < config.maxSquadTeams; i++) {
        const display = document.getElementById(`offense-${i}`);
        if (display) {
            const characters = Array.from(display.querySelectorAll('.character-image')).map(img => ({
                id: img.dataset.characterId,
                name: img.dataset.characterName,
                image: img.src
            }));
            if (characters.length > 0) {
                offenseTeams.push(characters);
            }
        }
    }
    
    // Collect fleet teams
    const fleetTeams = [];
    for (let i = 0; i < config.maxFleetTeams; i++) {
        const display = document.getElementById(`fleet-${i}`);
        if (display) {
            const characters = Array.from(display.querySelectorAll('.character-image')).map(img => ({
                id: img.dataset.characterId,
                name: img.dataset.characterName,
                image: img.src
            }));
            if (characters.length > 0) {
                fleetTeams.push(characters);
            }
        }
    }
    
    return {
        plan_name: document.getElementById('planName').value || 'Untitled Plan',
        league: league,
        format: format,
        defense_teams: defenseTeams,
        offense_teams: offenseTeams,
        fleet_teams: fleetTeams,
        notes: document.getElementById('notes').value
    };
}

function savePlan() {
    const planData = collectPlanData();
    
    if (!planData.plan_name) {
        alert('Please enter a plan name');
        return;
    }
    
    const savePromise = currentPlan.id 
        ? api.gac.update(currentPlan.id, planData)
        : api.gac.create(planData);
    
    savePromise
        .then(data => {
            if (data.id) currentPlan.id = data.id;
            showSuccess(data.message || 'Plan saved successfully');
        })
        .catch(error => {
            alert('Error saving plan: ' + (error.message || error));
        });
}

function loadPlan() {
    api.gac.getAll()
        .then(plans => {
            if (plans.length === 0) {
                alert('No saved plans found');
                return;
            }
            
            // Create modal for plan selection
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3>Load Plan</h3>
                        <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="max-height: 400px; overflow-y: auto;">
                            ${plans.map(plan => `
                                <div style="padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: background 0.2s;" 
                                     onmouseover="this.style.background='#f7fafc'" 
                                     onmouseout="this.style.background='white'"
                                     onclick="selectPlan(${plan.id})">
                                    <div style="font-weight: 600; margin-bottom: 5px;">${plan.plan_name}</div>
                                    <div style="font-size: 0.85rem; color: #718096;">
                                        ${plan.league.charAt(0).toUpperCase() + plan.league.slice(1)} - ${plan.format} | 
                                        Updated: ${new Date(plan.updated_at).toLocaleDateString()}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.remove();
                }
            });
        })
        .catch(error => {
            alert('Error loading plans: ' + (error.message || error));
        });
}

function selectPlan(planId) {
    api.gac.get(planId)
        .then(plan => {
            loadPlanData(plan);
            document.querySelectorAll('.modal').forEach(m => m.remove());
        })
        .catch(error => {
            alert('Error loading plan: ' + (error.message || error));
        });
}

// Load saved plan data into form
function loadPlanData(plan) {
    currentPlan = plan;
    document.getElementById('planName').value = plan.plan_name || '';
    document.getElementById('league').value = plan.league || 'kyber';
    document.getElementById('format').value = plan.format || '5v5';
    document.getElementById('notes').value = plan.notes || '';
    
    updateLayout();
    
    // Load defense teams
    if (plan.defense_teams && Array.isArray(plan.defense_teams)) {
        plan.defense_teams.forEach((territoryData, tIndex) => {
            if (territoryData.teams && Array.isArray(territoryData.teams)) {
                territoryData.teams.forEach((team, teamIndex) => {
                    const display = document.getElementById(`defense-${tIndex}-${teamIndex}`);
                    if (display && Array.isArray(team)) {
                        display.innerHTML = '';
                        team.forEach(char => {
                            const img = document.createElement('img');
                            img.className = 'character-image';
                            img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                            img.alt = char.name;
                            img.dataset.characterId = char.id;
                            img.dataset.characterName = char.name;
                            img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                            img.title = char.name;
                            display.appendChild(img);
                        });
                    }
                });
            }
        });
    }
    
    // Load offense teams
    if (plan.offense_teams && Array.isArray(plan.offense_teams)) {
        plan.offense_teams.forEach((team, index) => {
            const display = document.getElementById(`offense-${index}`);
            if (display && Array.isArray(team)) {
                display.innerHTML = '';
                team.forEach(char => {
                    const img = document.createElement('img');
                    img.className = 'character-image';
                    img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                    img.alt = char.name;
                    img.dataset.characterId = char.id;
                    img.dataset.characterName = char.name;
                    img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                    img.title = char.name;
                    display.appendChild(img);
                });
            }
        });
    }
    
    // Load fleet teams
    if (plan.fleet_teams && Array.isArray(plan.fleet_teams)) {
        plan.fleet_teams.forEach((team, index) => {
            const display = document.getElementById(`fleet-${index}`);
            if (display && Array.isArray(team)) {
                display.innerHTML = '';
                team.forEach(char => {
                    const img = document.createElement('img');
                    img.className = 'character-image';
                    img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                    img.alt = char.name;
                    img.dataset.characterId = char.id;
                    img.dataset.characterName = char.name;
                    img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                    img.title = char.name;
                    display.appendChild(img);
                });
            }
        });
    }
    
    updateCounts();
}

// Character Selection Modal
let currentTeamContext = null; // { type: 'defense'|'offense'|'fleet', territory: number, slot: number }
let allCharacters = [];
let filteredCharacters = [];
let selectedCharacters = [];

function openCharacterModal(type, territoryOrSlot, slot = null) {
    console.log('openCharacterModal called', type, territoryOrSlot, slot);
    
    try {
        currentTeamContext = { type, territory: territoryOrSlot, slot };
        selectedCharacters = [];
        
        // Load existing characters if any
        const displayId = slot !== null 
            ? `${type}-${territoryOrSlot}-${slot}`
            : `${type}-${territoryOrSlot}`;
        const display = document.getElementById(displayId);
        if (display) {
            display.querySelectorAll('.character-image').forEach(img => {
                selectedCharacters.push({
                    id: img.dataset.characterId,
                    name: img.dataset.characterName,
                    image: img.src
                });
            });
        }
        
        const modal = document.getElementById('characterModal');
        if (!modal) {
            console.error('Character modal not found!');
            alert('Character selection modal not found. Please refresh the page.');
            return;
        }
        
        modal.style.display = 'flex';
        document.getElementById('characterSearch').value = '';
        updateSelectedList();
        
        if (allCharacters.length === 0) {
            loadCharacters();
        } else {
            displayCharacters();
        }
    } catch (error) {
        console.error('Error opening character modal:', error);
        alert('Error opening character selection: ' + error.message);
    }
}

function closeCharacterModal() {
    document.getElementById('characterModal').style.display = 'none';
    currentTeamContext = null;
    selectedCharacters = [];
}

function loadCharacters() {
    const grid = document.getElementById('characterGrid');
    if (!grid) {
        console.error('Character grid not found!');
        return;
    }
    
    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px;">Loading characters...</div>';
    
    console.log('Loading characters from API...');
    
    if (typeof api === 'undefined' || !api.swgoh) {
        console.error('API not available!');
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #e53e3e;">API not loaded. Please refresh the page.</div>';
        return;
    }
    
    api.swgoh.getUnits()
        .then(characters => {
            console.log('Loaded characters:', characters.length);
            if (!Array.isArray(characters)) {
                console.error('Characters is not an array:', characters);
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #e53e3e;">Invalid data format received from API.</div>';
                return;
            }
            allCharacters = characters;
            filteredCharacters = characters;
            displayCharacters();
        })
        .catch(error => {
            console.error('Error loading characters:', error);
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #e53e3e;">Error loading characters: ${error.message || error}</div>`;
        });
}

function displayCharacters() {
    const grid = document.getElementById('characterGrid');
    if (!grid) return;
    
    const searchInput = document.getElementById('characterSearch');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    filteredCharacters = allCharacters.filter(char => {
        const name = char.name || '';
        const baseId = char.base_id || '';
        return name.toLowerCase().includes(searchTerm) || baseId.toLowerCase().includes(searchTerm);
    });
    
    if (filteredCharacters.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #718096;">No characters found</div>';
        return;
    }
    
    grid.innerHTML = filteredCharacters.map(char => {
        const charName = char.name || 'Unknown';
        const baseId = char.base_id || '';
        const imageUrl = char.image || `https://swgoh.gg/static/img/assets/tex.char_${baseId}.png`;
        const isSelected = selectedCharacters.some(c => c.id === baseId);
        
        // Escape the character data for onclick
        const charData = JSON.stringify({
            base_id: baseId,
            name: charName,
            image: imageUrl
        }).replace(/"/g, '&quot;');
        
        return `
            <div class="character-item ${isSelected ? 'selected' : ''}" onclick="toggleCharacterSafe('${baseId}', '${charName.replace(/'/g, "\\'")}', '${imageUrl.replace(/'/g, "\\'")}')">
                <img src="${imageUrl}" alt="${charName}" onerror="this.src='https://via.placeholder.com/80?text=?'" />
                <div class="character-name">${charName}</div>
            </div>
        `;
    }).join('');
}

function toggleCharacterSafe(baseId, name, image) {
    const character = {
        base_id: baseId,
        name: name,
        image: image
    };
    toggleCharacter(character);
}

function toggleCharacter(character) {
    const index = selectedCharacters.findIndex(c => c.id === character.base_id);
    if (index > -1) {
        selectedCharacters.splice(index, 1);
    } else {
        selectedCharacters.push({
            id: character.base_id,
            name: character.name,
            image: character.image || `https://swgoh.gg/static/img/assets/tex.char_${character.base_id}.png`
        });
    }
    updateSelectedList();
    displayCharacters();
}

function updateSelectedList() {
    const list = document.getElementById('selectedList');
    list.innerHTML = selectedCharacters.map(char => `
        <div class="selected-character">
            <img src="${char.image}" alt="${char.name}" onerror="this.src='https://via.placeholder.com/40?text=?'" />
            <span>${char.name}</span>
            <button type="button" onclick="removeSelectedCharacter('${char.id}')" style="background: #e53e3e; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; margin-left: 5px;">×</button>
        </div>
    `).join('') || '<span style="color: #718096;">No characters selected</span>';
}

function removeSelectedCharacter(characterId) {
    selectedCharacters = selectedCharacters.filter(c => c.id !== characterId);
    updateSelectedList();
    displayCharacters();
}

function confirmCharacterSelection() {
    if (!currentTeamContext) return;
    
    const { type, territory, slot } = currentTeamContext;
    const displayId = slot !== null 
        ? `${type}-${territory}-${slot}`
        : `${type}-${territory}`;
    const display = document.getElementById(displayId);
    
    if (display) {
        display.innerHTML = '';
        if (selectedCharacters.length > 0) {
            selectedCharacters.forEach(char => {
                const img = document.createElement('img');
                img.className = 'character-image';
                img.src = char.image;
                img.alt = char.name;
                img.dataset.characterId = char.id;
                img.dataset.characterName = char.name;
                img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                img.title = char.name;
                display.appendChild(img);
            });
        } else {
            display.innerHTML = '<span class="team-select-placeholder">Select Team</span>';
        }
    }
    
    updateCounts();
    closeCharacterModal();
}

// Search functionality - wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('characterSearch');
    if (searchInput) {
        searchInput.addEventListener('input', displayCharacters);
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('characterModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCharacterModal();
            }
        });
    }
});

// Make functions globally accessible
window.openCharacterModal = openCharacterModal;
window.closeCharacterModal = closeCharacterModal;
window.confirmCharacterSelection = confirmCharacterSelection;
window.toggleCharacter = toggleCharacter;
window.toggleCharacterSafe = toggleCharacterSafe;
window.removeSelectedCharacter = removeSelectedCharacter;
</script>

<?php require_once 'includes/footer.php'; ?>
