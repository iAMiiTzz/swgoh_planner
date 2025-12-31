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
            <div id="offenseTerritories" class="territories-grid">
                <!-- Territories will be generated here -->
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
    <div class="modal-content character-modal-content">
        <div class="modal-header">
            <h3>Select Team</h3>
            <button class="modal-close" onclick="closeCharacterModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="character-search-wrapper">
                <input type="text" id="characterSearch" class="character-search-input" placeholder="Search characters...">
            </div>
            <div id="characterGrid" class="character-grid">
                <!-- Characters will be loaded here -->
            </div>
            <div id="selectedCharacters" class="selected-characters-section">
                <div class="selected-section-header">Selected Team</div>
                <div class="selected-team-row">
                    <div class="selected-leader-section">
                        <div class="selected-label">
                            <span class="leader-icon">👑</span>
                            <span id="leaderLabel">Leader</span> <span class="required-badge">Required</span>
                        </div>
                        <div id="selectedLeader" class="selected-leader-container">
                            <span class="empty-state">No leader selected</span>
                        </div>
                    </div>
                    <div class="selected-members-section">
                        <div class="selected-label">
                            <span class="members-icon">⚔️</span>
                            <span id="membersLabel">Members</span>
                        </div>
                        <div id="selectedMembers" class="selected-members-container">
                            <span class="empty-state">No members selected</span>
                        </div>
                        <div id="memberLimit" class="member-limit-indicator"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeCharacterModal()" class="btn-secondary">Cancel</button>
            <button type="button" onclick="confirmCharacterSelection()" class="btn-primary">Confirm Selection</button>
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
                { name: 'Top Back', maxTeams: 3 },
                { name: 'Top Front', maxTeams: 4 },
                { name: 'Bottom Back', maxTeams: 3 },
                { name: 'Bottom Front', maxTeams: 4 }
            ]
        },
        '3v3': {
            maxSquadTeams: 15,
            maxFleetTeams: 3,
            territories: [
                { name: 'Top Back', maxTeams: 3 },
                { name: 'Top Front', maxTeams: 5 },
                { name: 'Bottom Back', maxTeams: 5 },
                { name: 'Bottom Front', maxTeams: 5 }
            ]
        }
    },
    aurodium: {
        '5v5': {
            maxSquadTeams: 9,
            maxFleetTeams: 2,
            territories: [
                { name: 'Top Back', maxTeams: 2 },
                { name: 'Top Front', maxTeams: 3 },
                { name: 'Bottom Back', maxTeams: 3 },
                { name: 'Bottom Front', maxTeams: 3 }
            ]
        },
        '3v3': {
            maxSquadTeams: 13,
            maxFleetTeams: 2,
            territories: [
                { name: 'Top Back', maxTeams: 2 },
                { name: 'Top Front', maxTeams: 4 },
                { name: 'Bottom Back', maxTeams: 5 },
                { name: 'Bottom Front', maxTeams: 4 }
            ]
        }
    },
    chromium: {
        '5v5': {
            maxSquadTeams: 7,
            maxFleetTeams: 2,
            territories: [
                { name: 'Top Back', maxTeams: 2 },
                { name: 'Top Front', maxTeams: 3 },
                { name: 'Bottom Back', maxTeams: 2 },
                { name: 'Bottom Front', maxTeams: 2 }
            ]
        },
        '3v3': {
            maxSquadTeams: 10,
            maxFleetTeams: 2,
            territories: [
                { name: 'Top Back', maxTeams: 2 },
                { name: 'Top Front', maxTeams: 3 },
                { name: 'Bottom Back', maxTeams: 4 },
                { name: 'Bottom Front', maxTeams: 3 }
            ]
        }
    },
    bronzium: {
        '5v5': {
            maxSquadTeams: 5,
            maxFleetTeams: 1,
            territories: [
                { name: 'Top Back', maxTeams: 1 },
                { name: 'Top Front', maxTeams: 2 },
                { name: 'Bottom Back', maxTeams: 1 },
                { name: 'Bottom Front', maxTeams: 2 }
            ]
        },
        '3v3': {
            maxSquadTeams: 7,
            maxFleetTeams: 1,
            territories: [
                { name: 'Top Back', maxTeams: 1 },
                { name: 'Top Front', maxTeams: 2 },
                { name: 'Bottom Back', maxTeams: 3 },
                { name: 'Bottom Front', maxTeams: 2 }
            ]
        }
    },
    carbonite: {
        '5v5': {
            maxSquadTeams: 3,
            maxFleetTeams: 1,
            territories: [
                { name: 'Top Back', maxTeams: 1 },
                { name: 'Top Front', maxTeams: 1 },
                { name: 'Bottom Back', maxTeams: 1 },
                { name: 'Bottom Front', maxTeams: 1 }
            ]
        },
        '3v3': {
            maxSquadTeams: 3,
            maxFleetTeams: 1,
            territories: [
                { name: 'Top Back', maxTeams: 1 },
                { name: 'Top Front', maxTeams: 1 },
                { name: 'Bottom Back', maxTeams: 1 },
                { name: 'Bottom Front', maxTeams: 1 }
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

let autoSaveTimer = null;
let isAutoSaving = false;
let lastSavedData = null;
let isInitialLoad = true;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load most recent plan automatically
    loadMostRecentPlan();
    
    // Set up auto-save listeners
    document.getElementById('planName').addEventListener('input', autoSave);
    document.getElementById('planName').addEventListener('change', autoSave);
    document.getElementById('notes').addEventListener('input', autoSave);
    document.getElementById('notes').addEventListener('change', autoSave);
    
    document.getElementById('league').addEventListener('change', function() {
        updateLayout();
        autoSave();
        // Clear any open modal selections when format/league changes
        if (currentTeamContext) {
            selectedLeader = null;
            selectedMembers = [];
            updateSelectedList();
        }
    });
    document.getElementById('format').addEventListener('change', function() {
        updateLayout();
        autoSave();
        // Update member limit if modal is open
        if (currentTeamContext) {
            updateMemberLimit();
            // Remove excess members if format changed to 3v3
            const maxMembers = document.getElementById('format').value === '5v5' ? 4 : 2;
            if (selectedMembers.length > maxMembers) {
                selectedMembers = selectedMembers.slice(0, maxMembers);
                updateSelectedList();
                displayCharacters();
            }
        }
    });
    updateLayout();
});

function loadMostRecentPlan() {
    api.gac.getAll()
        .then(plans => {
            if (plans.length > 0) {
                // Sort by updated_at descending and get the most recent
                plans.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                const mostRecent = plans[0];
                loadPlanData(mostRecent);
                lastSavedData = JSON.stringify(collectPlanData());
            } else {
                // No plans exist, create a default one
                const defaultPlanName = 'My GAC Plan';
                document.getElementById('planName').value = defaultPlanName;
                // Save it after a short delay to ensure everything is initialized
                setTimeout(() => {
                    savePlan(false).then(() => {
                        lastSavedData = JSON.stringify(collectPlanData());
                        isInitialLoad = false; // Enable auto-save after initial save
                    });
                }, 1000);
            }
            // Enable auto-save after loading is complete
            setTimeout(() => {
                isInitialLoad = false;
            }, 1500);
        })
        .catch(error => {
            console.error('Error loading most recent plan:', error);
            // If error, still try to create a default plan
            const defaultPlanName = 'My GAC Plan';
            document.getElementById('planName').value = defaultPlanName;
            setTimeout(() => {
                savePlan(false).then(() => {
                    lastSavedData = JSON.stringify(collectPlanData());
                    isInitialLoad = false; // Enable auto-save after initial save
                });
            }, 1000);
        });
}

function updateLayout() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Update info display
    document.getElementById('maxSquadTeams').textContent = config.maxSquadTeams;
    document.getElementById('maxFleetTeams').textContent = config.maxFleetTeams;
    
    // Generate defense territories
    generateDefenseTerritories(config.territories);
    
    // Generate offense territories (same structure as defense)
    generateOffenseTerritories(config.territories);
    
    updateCounts();
    
    // Auto-save when layout changes (territories are regenerated)
    autoSave();
}

function generateDefenseTerritories(territories) {
    const container = document.getElementById('defenseTerritories');
    container.innerHTML = '';
    
    territories.forEach((territory, index) => {
        const territoryDiv = document.createElement('div');
        territoryDiv.className = 'territory-card';
        
        // Top Back (index 0) is ships only, no character teams
        if (territory.name === 'Top Back') {
            const league = document.getElementById('league').value;
            const format = document.getElementById('format').value;
            const config = GAC_CONFIG[league][format];
            const maxFleetTeams = config.maxFleetTeams;
            
            territoryDiv.innerHTML = `
                <h4>${territory.name} (Fleet)</h4>
                <div class="territory-teams" data-territory="${index}">
                    ${Array(maxFleetTeams).fill(0).map((_, i) => `
                        <div class="team-slot" data-territory="${index}" data-slot="${i}">
                            <div class="team-header">Fleet ${i + 1}</div>
                            <button type="button" class="team-select-button" onclick="openCharacterModal('fleet', ${index}, ${i})">
                                <div class="team-characters-display" id="fleet-defense-${i}">
                                    <span class="team-select-placeholder">Select Fleet</span>
                                </div>
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            territoryDiv.innerHTML = `
                <h4>${territory.name}</h4>
                <div class="territory-teams" data-territory="${index}">
                    ${Array(territory.maxTeams).fill(0).map((_, i) => `
                        <div class="team-slot" data-territory="${index}" data-slot="${i}">
                            <div class="team-header">Team ${i + 1}</div>
                            <button type="button" class="team-select-button" onclick="openCharacterModal('defense', ${index}, ${i})">
                                <div class="team-characters-display" id="defense-${index}-${i}">
                                    <span class="team-select-placeholder">Select Team</span>
                                </div>
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        container.appendChild(territoryDiv);
    });
}

function generateOffenseTerritories(territories) {
    const container = document.getElementById('offenseTerritories');
    container.innerHTML = '';
    
    // Map offense territory indices (swap 0↔1 and 2↔3) to get opposite teams
    const offenseTerritoryIndexMap = {
        0: 1,  // Top Back (index 0) → use Top Front's teams (index 1)
        1: 0,  // Top Front (index 1) → use Top Back's teams (index 0)
        2: 3,  // Bottom Back (index 2) → use Bottom Front's teams (index 3)
        3: 2   // Bottom Front (index 3) → use Bottom Back's teams (index 2)
    };
    
    // Map offense territory names (swap Top Front/Top Back and Bottom Back/Bottom Front)
    const offenseTerritoryNames = {
        'Top Back': 'Top Front',
        'Top Front': 'Top Back',
        'Bottom Back': 'Bottom Front',
        'Bottom Front': 'Bottom Back'
    };
    
    territories.forEach((territory, index) => {
        // Get the swapped territory to use its maxTeams
        const swappedIndex = offenseTerritoryIndexMap[index] !== undefined ? offenseTerritoryIndexMap[index] : index;
        const swappedTerritory = territories[swappedIndex];
        const maxTeams = swappedTerritory ? swappedTerritory.maxTeams : territory.maxTeams;
        
        const territoryDiv = document.createElement('div');
        territoryDiv.className = 'territory-card';
        // Use swapped name for offense
        const displayName = offenseTerritoryNames[territory.name] || territory.name;
        
        // Top Back on offense (which is index 1, displays as "Top Back") is ships only
        if (displayName === 'Top Back') {
            const league = document.getElementById('league').value;
            const format = document.getElementById('format').value;
            const config = GAC_CONFIG[league][format];
            const maxFleetTeams = config.maxFleetTeams;
            
            territoryDiv.innerHTML = `
                <h4>${displayName} (Fleet)</h4>
                <div class="territory-teams" data-territory="${index}">
                    ${Array(maxFleetTeams).fill(0).map((_, i) => `
                        <div class="team-slot" data-territory="${index}" data-slot="${i}">
                            <div class="team-header">Fleet ${i + 1}</div>
                            <button type="button" class="team-select-button" onclick="openCharacterModal('fleet', ${index}, ${i})">
                                <div class="team-characters-display" id="fleet-offense-${i}">
                                    <span class="team-select-placeholder">Select Fleet</span>
                                </div>
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            territoryDiv.innerHTML = `
                <h4>${displayName}</h4>
                <div class="territory-teams" data-territory="${index}">
                    ${Array(maxTeams).fill(0).map((_, i) => `
                        <div class="team-slot" data-territory="${index}" data-slot="${i}">
                            <div class="team-header">Team ${i + 1}</div>
                            <button type="button" class="team-select-button" onclick="openCharacterModal('offense', ${index}, ${i})">
                                <div class="team-characters-display" id="offense-${index}-${i}">
                                    <span class="team-select-placeholder">Select Team</span>
                                </div>
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        container.appendChild(territoryDiv);
    });
}


function updateCounts() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Count defense teams (teams with characters, excluding Top Back which is ships only)
    let defenseCount = 0;
    document.querySelectorAll('#defenseTerritories .team-characters-display').forEach(display => {
        // Skip Top Back territory (check if parent territory card contains "Top Back")
        const territoryCard = display.closest('.territory-card');
        if (territoryCard && territoryCard.querySelector('h4') && territoryCard.querySelector('h4').textContent === 'Top Back') {
            return;
        }
        if (display.querySelector('.character-image')) defenseCount++;
    });
    
    // Count offense teams (by territory, excluding Top Back which is ships only)
    let offenseCount = 0;
    document.querySelectorAll('#offenseTerritories .team-characters-display').forEach(display => {
        // Skip Top Back territory (check if parent territory card contains "Top Back")
        const territoryCard = display.closest('.territory-card');
        if (territoryCard && territoryCard.querySelector('h4') && territoryCard.querySelector('h4').textContent === 'Top Back') {
            return;
        }
        if (display.querySelector('.character-image')) offenseCount++;
    });
    
    // Count fleet teams from Top Back sections
    let fleetCount = 0;
    // Count from defense Top Back
    document.querySelectorAll('#defenseTerritories .territory-card').forEach(card => {
        const h4 = card.querySelector('h4');
        if (h4 && h4.textContent.includes('Top Back')) {
            card.querySelectorAll('.team-characters-display').forEach(display => {
                if (display.querySelector('.character-image')) fleetCount++;
            });
        }
    });
    // Count from offense Top Back
    document.querySelectorAll('#offenseTerritories .territory-card').forEach(card => {
        const h4 = card.querySelector('h4');
        if (h4 && h4.textContent.includes('Top Back')) {
            card.querySelectorAll('.team-characters-display').forEach(display => {
                if (display.querySelector('.character-image')) fleetCount++;
            });
        }
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
        // Skip Top Back - it's ships only
        if (territory.name === 'Top Back') {
            defenseTeams.push({
                territory: territory.name,
                teams: []
            });
            return;
        }
        
        const teams = [];
        for (let i = 0; i < territory.maxTeams; i++) {
            const display = document.getElementById(`defense-${tIndex}-${i}`);
            if (display) {
                const leaderImg = display.querySelector('.character-image.leader');
                const memberImgs = display.querySelectorAll('.character-image.member');
                
                if (leaderImg || memberImgs.length > 0) {
                    const team = {
                        leader: leaderImg ? {
                            id: leaderImg.dataset.characterId,
                            name: leaderImg.dataset.characterName,
                            image: leaderImg.src
                        } : null,
                        members: Array.from(memberImgs).map(img => ({
                            id: img.dataset.characterId,
                            name: img.dataset.characterName,
                            image: img.src
                        }))
                    };
                    teams.push(team);
                }
            }
        }
        defenseTeams.push({
            territory: territory.name,
            teams: teams
        });
    });
    
    // Collect offense teams by territory
    const offenseTeams = [];
    // Map offense territory indices (swap 0↔1 and 2↔3)
    const offenseTerritoryIndexMap = {
        0: 1,  // Top Back (index 0) → Top Front (index 1)
        1: 0,  // Top Front (index 1) → Top Back (index 0)
        2: 3,  // Bottom Back (index 2) → Bottom Front (index 3)
        3: 2   // Bottom Front (index 3) → Bottom Back (index 2)
    };
    // Map offense territory names (swap Top Front/Top Back and Bottom Back/Bottom Front)
    const offenseTerritoryNames = {
        'Top Back': 'Top Front',
        'Top Front': 'Top Back',
        'Bottom Back': 'Bottom Front',
        'Bottom Front': 'Bottom Back'
    };
    
    // Create array with swapped positions
    const swappedOffenseTeams = [];
    config.territories.forEach((territory, tIndex) => {
        // Get the swapped territory to use its maxTeams
        const swappedIndex = offenseTerritoryIndexMap[tIndex] !== undefined ? offenseTerritoryIndexMap[tIndex] : tIndex;
        const swappedTerritory = config.territories[swappedIndex];
        const maxTeams = swappedTerritory ? swappedTerritory.maxTeams : territory.maxTeams;
        
        const teams = [];
        for (let i = 0; i < maxTeams; i++) {
            const display = document.getElementById(`offense-${tIndex}-${i}`);
            if (display) {
                const leaderImg = display.querySelector('.character-image.leader');
                const memberImgs = display.querySelectorAll('.character-image.member');
                
                if (leaderImg || memberImgs.length > 0) {
                    const team = {
                        leader: leaderImg ? {
                            id: leaderImg.dataset.characterId,
                            name: leaderImg.dataset.characterName,
                            image: leaderImg.src
                        } : null,
                        members: Array.from(memberImgs).map(img => ({
                            id: img.dataset.characterId,
                            name: img.dataset.characterName,
                            image: img.src
                        }))
                    };
                    teams.push(team);
                }
            }
        }
        swappedOffenseTeams[tIndex] = {
            territory: territory.name,
            teams: teams
        };
    });
    
    // Reorder using the swap map
    config.territories.forEach((territory, tIndex) => {
        const swappedIndex = offenseTerritoryIndexMap[tIndex] !== undefined ? offenseTerritoryIndexMap[tIndex] : tIndex;
        const swappedData = swappedOffenseTeams[swappedIndex] || { territory: territory.name, teams: [] };
        const displayName = offenseTerritoryNames[swappedData.territory] || swappedData.territory;
        
        // Top Back on offense is ships only, skip character teams
        if (displayName === 'Top Back') {
            offenseTeams.push({
                territory: displayName,
                teams: []
            });
            return;
        }
        
        offenseTeams.push({
            territory: displayName,
            teams: swappedData.teams
        });
    });
    
    // Collect fleet teams from Top Back sections
    const fleetTeams = [];
    // Collect from defense Top Back
    for (let i = 0; i < config.maxFleetTeams; i++) {
        const display = document.getElementById(`fleet-defense-${i}`);
        if (display) {
            const leaderImg = display.querySelector('.character-image.leader');
            const memberImgs = display.querySelectorAll('.character-image.member');
            
            if (leaderImg || memberImgs.length > 0) {
                const team = {
                    leader: leaderImg ? {
                        id: leaderImg.dataset.characterId,
                        name: leaderImg.dataset.characterName,
                        image: leaderImg.src
                    } : null,
                    members: Array.from(memberImgs).map(img => ({
                        id: img.dataset.characterId,
                        name: img.dataset.characterName,
                        image: img.src
                    }))
                };
                fleetTeams.push(team);
            }
        }
    }
    // Collect from offense Top Back
    for (let i = 0; i < config.maxFleetTeams; i++) {
        const display = document.getElementById(`fleet-offense-${i}`);
        if (display) {
            const leaderImg = display.querySelector('.character-image.leader');
            const memberImgs = display.querySelectorAll('.character-image.member');
            
            if (leaderImg || memberImgs.length > 0) {
                const team = {
                    leader: leaderImg ? {
                        id: leaderImg.dataset.characterId,
                        name: leaderImg.dataset.characterName,
                        image: leaderImg.src
                    } : null,
                    members: Array.from(memberImgs).map(img => ({
                        id: img.dataset.characterId,
                        name: img.dataset.characterName,
                        image: img.src
                    }))
                };
                fleetTeams.push(team);
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

function savePlan(showMessage = true) {
    const planData = collectPlanData();
    
    // Ensure plan has a name
    if (!planData.plan_name || planData.plan_name.trim() === '') {
        planData.plan_name = 'Untitled Plan';
    }
    
    // Check if data has actually changed
    const dataString = JSON.stringify(planData);
    if (lastSavedData === dataString) {
        return Promise.resolve(); // No changes, skip save
    }
    
    isAutoSaving = true;
    updateSaveStatus('Saving...');
    
    const savePromise = currentPlan.id 
        ? api.gac.update(currentPlan.id, planData)
        : api.gac.create(planData);
    
    return savePromise
        .then(data => {
            if (data.id) currentPlan.id = data.id;
            lastSavedData = dataString;
            isAutoSaving = false;
            updateSaveStatus('Saved');
            if (showMessage) {
                showSuccess(data.message || 'Plan saved successfully');
            }
            // Clear status after 2 seconds
            setTimeout(() => {
                updateSaveStatus('');
            }, 2000);
            return data;
        })
        .catch(error => {
            isAutoSaving = false;
            updateSaveStatus('Error saving');
            if (showMessage) {
                alert('Error saving plan: ' + (error.message || error));
            }
            console.error('Auto-save error:', error);
            throw error;
        });
}

function autoSave() {
    // Don't auto-save during initial load
    if (isInitialLoad) {
        return;
    }
    
    // Clear existing timer
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }
    
    // Set new timer (debounce: wait 2 seconds after last change)
    autoSaveTimer = setTimeout(() => {
        if (!isAutoSaving) {
            savePlan(false).catch(() => {
                // Error already handled in savePlan
            });
        }
    }, 2000);
}

function updateSaveStatus(message) {
    const statusElement = document.getElementById('saveStatus');
    if (statusElement) {
        statusElement.textContent = message;
        if (message === 'Saved') {
            statusElement.style.color = '#38a169';
        } else if (message === 'Saving...') {
            statusElement.style.color = '#667eea';
        } else if (message === 'Error saving') {
            statusElement.style.color = '#e53e3e';
        } else {
            statusElement.style.color = '';
        }
    }
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
            // Skip Top Back - it's ships only
            if (territoryData.territory === 'Top Back') {
                return;
            }
            
            if (territoryData.teams && Array.isArray(territoryData.teams)) {
                territoryData.teams.forEach((team, teamIndex) => {
                    const display = document.getElementById(`defense-${tIndex}-${teamIndex}`);
                    if (display) {
                        display.innerHTML = '';
                        
                        // Handle new format (leader + members)
                        if (team.leader || (team.members && Array.isArray(team.members))) {
                            // New format
                            if (team.leader) {
                                const img = document.createElement('img');
                                img.className = 'character-image leader';
                                img.src = team.leader.image || `https://swgoh.gg/static/img/assets/tex.char_${team.leader.id}.png`;
                                img.alt = team.leader.name;
                                img.dataset.characterId = team.leader.id;
                                img.dataset.characterName = team.leader.name;
                                img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                img.title = team.leader.name + ' (Leader) - Click to edit';
                                img.style.cursor = 'pointer';
                                img.onclick = function() {
                                    openCharacterModal('defense', tIndex, teamIndex);
                                };
                                display.appendChild(img);
                            }
                            
                            if (team.members && Array.isArray(team.members)) {
                                team.members.forEach(char => {
                                    const img = document.createElement('img');
                                    img.className = 'character-image member';
                                    img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                                    img.alt = char.name;
                                    img.dataset.characterId = char.id;
                                    img.dataset.characterName = char.name;
                                    img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                    img.title = char.name + ' - Click to edit';
                                    img.style.cursor = 'pointer';
                                    img.onclick = function() {
                                        openCharacterModal('defense', tIndex, teamIndex);
                                    };
                                    display.appendChild(img);
                                });
                            }
                        } else if (Array.isArray(team)) {
                            // Old format - just array of characters (backward compatibility)
                            team.forEach((char, charIndex) => {
                                const img = document.createElement('img');
                                img.className = charIndex === 0 ? 'character-image leader' : 'character-image member';
                                img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                                img.alt = char.name;
                                img.dataset.characterId = char.id;
                                img.dataset.characterName = char.name;
                                img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                img.title = charIndex === 0 ? char.name + ' (Leader) - Click to edit' : char.name + ' - Click to edit';
                                img.style.cursor = 'pointer';
                                img.onclick = function() {
                                    openCharacterModal('defense', tIndex, teamIndex);
                                };
                                display.appendChild(img);
                            });
                        }
                    }
                });
            }
        });
    }
    
    // Load offense teams (handle both old format and new territory format)
    if (plan.offense_teams && Array.isArray(plan.offense_teams)) {
        // Check if it's the new territory format
        if (plan.offense_teams.length > 0 && plan.offense_teams[0].territory) {
            // Map offense territory indices (swap 0↔1 and 2↔3) - reverse of save
            const offenseTerritoryIndexMap = {
                0: 1,  // Load from index 1 to display at index 0
                1: 0,  // Load from index 0 to display at index 1
                2: 3,  // Load from index 3 to display at index 2
                3: 2   // Load from index 2 to display at index 3
            };
            
            // New territory-based format
            plan.offense_teams.forEach((territoryData, savedIndex) => {
                // Skip Top Back - it's ships only
                if (territoryData.territory === 'Top Back') {
                    return;
                }
                
                // Map saved index to display index
                const displayIndex = offenseTerritoryIndexMap[savedIndex] !== undefined ? offenseTerritoryIndexMap[savedIndex] : savedIndex;
                
                if (territoryData.teams && Array.isArray(territoryData.teams)) {
                    territoryData.teams.forEach((team, teamIndex) => {
                        const display = document.getElementById(`offense-${displayIndex}-${teamIndex}`);
                        if (display) {
                            display.innerHTML = '';
                            
                            // Handle new format (leader + members)
                            if (team.leader || (team.members && Array.isArray(team.members))) {
                                // New format
                                if (team.leader) {
                                    const img = document.createElement('img');
                                    img.className = 'character-image leader';
                                    img.src = team.leader.image || `https://swgoh.gg/static/img/assets/tex.char_${team.leader.id}.png`;
                                    img.alt = team.leader.name;
                                    img.dataset.characterId = team.leader.id;
                                    img.dataset.characterName = team.leader.name;
                                    img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                    img.title = team.leader.name + ' (Leader) - Click to edit';
                                    img.style.cursor = 'pointer';
                                    img.onclick = function() {
                                        openCharacterModal('offense', displayIndex, teamIndex);
                                    };
                                    display.appendChild(img);
                                }
                                
                                if (team.members && Array.isArray(team.members)) {
                                    team.members.forEach(char => {
                                        const img = document.createElement('img');
                                        img.className = 'character-image member';
                                        img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                                        img.alt = char.name;
                                        img.dataset.characterId = char.id;
                                        img.dataset.characterName = char.name;
                                        img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                        img.title = char.name + ' - Click to edit';
                                        img.style.cursor = 'pointer';
                                        img.onclick = function() {
                                            openCharacterModal('offense', displayIndex, teamIndex);
                                        };
                                        display.appendChild(img);
                                    });
                                }
                            } else if (Array.isArray(team)) {
                                // Old format - just array of characters (backward compatibility)
                                team.forEach((char, charIndex) => {
                                    const img = document.createElement('img');
                                    img.className = charIndex === 0 ? 'character-image leader' : 'character-image member';
                                    img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                                    img.alt = char.name;
                                    img.dataset.characterId = char.id;
                                    img.dataset.characterName = char.name;
                                    img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                                    img.title = charIndex === 0 ? char.name + ' (Leader) - Click to edit' : char.name + ' - Click to edit';
                                    img.style.cursor = 'pointer';
                                    img.onclick = function() {
                                        openCharacterModal('offense', displayIndex, teamIndex);
                                    };
                                    display.appendChild(img);
                                });
                            }
                        }
                    });
                }
            });
        } else {
            // Old format - just array of teams (for backward compatibility)
            // Map offense territory indices (swap 0↔1 and 2↔3) - reverse of save
            const offenseTerritoryIndexMap = {
                0: 1,  // Load from index 1 to display at index 0
                1: 0,  // Load from index 0 to display at index 1
                2: 3,  // Load from index 3 to display at index 2
                3: 2   // Load from index 2 to display at index 3
            };
            
            plan.offense_teams.forEach((team, index) => {
                // Try to find a matching slot in territories
                const league = document.getElementById('league').value;
                const format = document.getElementById('format').value;
                const config = GAC_CONFIG[league][format];
                let slotIndex = 0;
                let savedTerritoryIndex = 0;
                
                // Distribute teams across territories (using saved positions)
                for (let t = 0; t < config.territories.length && slotIndex < index; t++) {
                    if (slotIndex + config.territories[t].maxTeams > index) {
                        savedTerritoryIndex = t;
                        break;
                    }
                    slotIndex += config.territories[t].maxTeams;
                }
                
                // Map saved territory index to display index
                const displayTerritoryIndex = offenseTerritoryIndexMap[savedTerritoryIndex] !== undefined ? offenseTerritoryIndexMap[savedTerritoryIndex] : savedTerritoryIndex;
                const teamSlotIndex = index - slotIndex;
                const display = document.getElementById(`offense-${displayTerritoryIndex}-${teamSlotIndex}`);
                if (display && Array.isArray(team)) {
                    display.innerHTML = '';
                    team.forEach((char, charIndex) => {
                        const img = document.createElement('img');
                        img.className = charIndex === 0 ? 'character-image leader' : 'character-image member';
                        img.src = char.image || `https://swgoh.gg/static/img/assets/tex.char_${char.id}.png`;
                        img.alt = char.name;
                        img.dataset.characterId = char.id;
                        img.dataset.characterName = char.name;
                        img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                        img.title = charIndex === 0 ? char.name + ' (Leader) - Click to edit' : char.name + ' - Click to edit';
                        img.style.cursor = 'pointer';
                        img.onclick = function() {
                            openCharacterModal('offense', displayTerritoryIndex, teamSlotIndex);
                        };
                        display.appendChild(img);
                    });
                }
            });
        }
    }
    
    // Load fleet teams into Top Back sections
    if (plan.fleet_teams && Array.isArray(plan.fleet_teams)) {
        const league = document.getElementById('league').value;
        const format = document.getElementById('format').value;
        const config = GAC_CONFIG[league][format];
        const maxFleetTeams = config.maxFleetTeams;
        
        // Split fleet teams between defense and offense
        plan.fleet_teams.forEach((team, index) => {
            if (Array.isArray(team) && team.length > 0) {
                // First half goes to defense, second half to offense
                const isDefense = index < maxFleetTeams;
                const fleetIndex = isDefense ? index : index - maxFleetTeams;
                const displayId = isDefense ? `fleet-defense-${fleetIndex}` : `fleet-offense-${fleetIndex}`;
                const display = document.getElementById(displayId);
                
                if (display) {
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
                        img.style.cursor = 'pointer';
                        img.onclick = function() {
                            const territoryIndex = isDefense ? 0 : 1; // Top Back is index 0 for defense, index 1 for offense
                            openCharacterModal('fleet', territoryIndex, fleetIndex);
                        };
                        display.appendChild(img);
                    });
                }
            }
        });
    }
    
    updateCounts();
}

// Character Selection Modal
let currentTeamContext = null; // { type: 'defense'|'offense'|'fleet', territory: number, slot: number }
let allCharacters = [];
let filteredCharacters = [];
let selectedLeader = null;
let selectedMembers = [];
let usedDefenseCharacters = new Map(); // Track characters already used in defense: characterId -> {territory: string, team: number}

// Get all characters currently used in defense territories (excluding current team)
function getUsedDefenseCharacters(excludeTerritory, excludeSlot) {
    const used = new Map();
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    config.territories.forEach((territory, tIndex) => {
        for (let i = 0; i < territory.maxTeams; i++) {
            // Skip the current team being edited
            if (tIndex === excludeTerritory && i === excludeSlot) {
                continue;
            }
            
            const display = document.getElementById(`defense-${tIndex}-${i}`);
            if (display) {
                const territoryName = territory.name;
                const teamNumber = i + 1;
                
                // Get leader
                const leaderImg = display.querySelector('.character-image.leader');
                if (leaderImg && leaderImg.dataset.characterId) {
                    used.set(leaderImg.dataset.characterId, {
                        territory: territoryName,
                        team: teamNumber
                    });
                }
                
                // Get members
                const memberImgs = display.querySelectorAll('.character-image.member');
                memberImgs.forEach(img => {
                    if (img.dataset.characterId) {
                        used.set(img.dataset.characterId, {
                            territory: territoryName,
                            team: teamNumber
                        });
                    }
                });
                
                // Fallback for old format (no leader/member classes)
                if (!leaderImg && memberImgs.length === 0) {
                    const allImgs = display.querySelectorAll('.character-image');
                    allImgs.forEach(img => {
                        if (img.dataset.characterId) {
                            used.set(img.dataset.characterId, {
                                territory: territoryName,
                                team: teamNumber
                            });
                        }
                    });
                }
            }
        }
    });
    
    return used;
}

function openCharacterModal(type, territoryOrSlot, slot = null) {
    console.log('openCharacterModal called', type, territoryOrSlot, slot);
    
    try {
        currentTeamContext = { type, territory: territoryOrSlot, slot };
        selectedLeader = null;
        selectedMembers = [];
        
        // If editing defense, get characters already used in other defense teams
        // Fleet teams don't need duplicate checking
        if (type === 'defense') {
            usedDefenseCharacters = getUsedDefenseCharacters(territoryOrSlot, slot);
        } else if (type === 'fleet') {
            usedDefenseCharacters = new Map(); // No duplicate checking for fleet
        } else {
            usedDefenseCharacters = new Map();
        }
        
        // Load existing characters if any
        let displayId;
        if (type === 'fleet') {
            // Fleet teams from Top Back sections
            const isDefense = territoryOrSlot === 0; // Top Back defense is index 0
            displayId = isDefense ? `fleet-defense-${slot}` : `fleet-offense-${slot}`;
        } else {
            displayId = slot !== null 
                ? `${type}-${territoryOrSlot}-${slot}`
                : `${type}-${territoryOrSlot}`;
        }
        const display = document.getElementById(displayId);
        if (display) {
            // Find leader (first image with leader class, or first image if no class)
            const leaderImg = display.querySelector('.character-image.leader') || 
                             (display.querySelectorAll('.character-image').length > 0 ? display.querySelectorAll('.character-image')[0] : null);
            if (leaderImg) {
                selectedLeader = {
                    id: leaderImg.dataset.characterId,
                    name: leaderImg.dataset.characterName,
                    image: leaderImg.src
                };
            }
            
            // Find members (all images with member class, or all images after first if no class)
            const memberImgs = display.querySelectorAll('.character-image.member');
            if (memberImgs.length > 0) {
                memberImgs.forEach(img => {
                    selectedMembers.push({
                        id: img.dataset.characterId,
                        name: img.dataset.characterName,
                        image: img.src
                    });
                });
            } else if (display.querySelectorAll('.character-image').length > 1) {
                // Fallback: if no member class, treat all after first as members
                const allImgs = Array.from(display.querySelectorAll('.character-image'));
                allImgs.slice(1).forEach(img => {
                    selectedMembers.push({
                        id: img.dataset.characterId,
                        name: img.dataset.characterName,
                        image: img.src
                    });
                });
            }
        }
        
        const modal = document.getElementById('characterModal');
        if (!modal) {
            console.error('Character modal not found!');
            alert('Character selection modal not found. Please refresh the page.');
            return;
        }
        
        console.log('Opening modal...');
        modal.style.display = 'flex';
        modal.style.zIndex = '1000';
        
        // Update modal title and labels for fleet teams
        const modalTitle = document.querySelector('#characterModal .modal-header h3');
        const leaderLabel = document.getElementById('leaderLabel');
        const membersLabel = document.getElementById('membersLabel');
        
        if (type === 'fleet') {
            if (modalTitle) modalTitle.textContent = 'Select Fleet';
            if (leaderLabel) leaderLabel.textContent = 'Capital Ship';
            if (membersLabel) membersLabel.textContent = 'Ships';
        } else {
            if (modalTitle) modalTitle.textContent = 'Select Team';
            if (leaderLabel) leaderLabel.textContent = 'Leader';
            if (membersLabel) membersLabel.textContent = 'Members';
        }
        
        // Force modal to be visible
        setTimeout(() => {
            if (modal.style.display !== 'flex') {
                modal.style.display = 'flex';
            }
        }, 100);
        document.getElementById('characterSearch').value = '';
        updateSelectedList();
        
        // Fleet teams don't need member limits
        if (type !== 'fleet') {
            updateMemberLimit();
        }
        
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
    selectedLeader = null;
    selectedMembers = [];
}

function loadCharacters() {
    const grid = document.getElementById('characterGrid');
    if (!grid) {
        console.error('Character grid not found!');
        return;
    }
    
            grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #718096;"><div style="font-size: 1.2rem; margin-bottom: 8px;">⏳</div><div>Loading characters...</div></div>';
    
    console.log('Loading characters from API...');
    
    if (typeof api === 'undefined' || !api.swgoh) {
        console.error('API not available!');
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #e53e3e;">API not loaded. Please refresh the page.</div>';
        return;
    }
    
    api.swgoh.getUnits()
        .then(response => {
            console.log('API Response:', response);
            
            // Handle different response formats
            let characters = [];
            if (Array.isArray(response)) {
                characters = response;
            } else if (response && typeof response === 'object') {
                // Check if it's an object with a data property
                if (response.data && Array.isArray(response.data)) {
                    characters = response.data;
                } else if (response.units && Array.isArray(response.units)) {
                    characters = response.units;
                } else if (response.results && Array.isArray(response.results)) {
                    characters = response.results;
                } else {
                    // Try to convert object values to array
                    characters = Object.values(response).filter(item => item && typeof item === 'object');
                }
            }
            
            if (!Array.isArray(characters) || characters.length === 0) {
                console.error('Could not parse characters from response:', response);
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #e53e3e;"><div style="font-size: 1.2rem; margin-bottom: 8px;">⚠️</div><div>Invalid data format received from API.</div><div style="font-size: 0.85rem; margin-top: 8px; color: #718096;">Check console for details.</div></div>';
                return;
            }
            
            console.log('Parsed characters:', characters.length);
            allCharacters = characters;
            filteredCharacters = characters;
            displayCharacters();
        })
        .catch(error => {
            console.error('Error loading characters:', error);
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 20px; color: #e53e3e;">Error loading characters: ${error.message || error}</div>`;
        });
}

// Helper function to check if a unit is a ship
function isShip(char) {
    if (!char) return false;
    const combatType = char.combat_type;
    const category = char.category || char.unit_category;
    const baseId = (char.base_id || char.id || '').toUpperCase();
    
    return combatType === 2 || 
           category === 'ship' || 
           category === 'SHIP' ||
           baseId.includes('SHIP') ||
           baseId.includes('CAPITAL');
}

// Helper function to check if a ship is a Capital ship
function isCapitalShip(char) {
    if (!isShip(char)) return false;
    const baseId = (char.base_id || char.id || '').toUpperCase();
    const name = (char.name || char.unit_name || '').toUpperCase();
    
    // Capital ships typically have CAPITAL in their ID or are specific capital ships
    return baseId.includes('CAPITAL') ||
           baseId.includes('EXECUTOR') ||
           baseId.includes('PROFUNDITY') ||
           baseId.includes('NEGOTIATOR') ||
           baseId.includes('HOMEONE') ||
           baseId.includes('CHIMAERA') ||
           baseId.includes('RAVAGER') ||
           baseId.includes('MALEVOLENCE') ||
           name.includes('CAPITAL') ||
           name.includes('EXECUTOR') ||
           name.includes('PROFUNDITY') ||
           name.includes('NEGOTIATOR') ||
           name.includes('HOME ONE') ||
           name.includes('CHIMAERA') ||
           name.includes('RAVAGER') ||
           name.includes('MALEVOLENCE');
}

function displayCharacters() {
    const grid = document.getElementById('characterGrid');
    if (!grid) {
        console.error('Character grid not found in displayCharacters');
        return;
    }
    
    const searchInput = document.getElementById('characterSearch');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    // Filter for ships only if selecting fleet teams
    const isFleetSelection = currentTeamContext && currentTeamContext.type === 'fleet';
    
    filteredCharacters = allCharacters.filter(char => {
        if (!char) return false;
        
        // If selecting fleet, only show ships
        if (isFleetSelection) {
            if (!isShip(char)) return false;
            
            // If no leader selected, only show Capital ships
            if (!selectedLeader) {
                return isCapitalShip(char);
            }
            // If leader is selected, only show regular ships (not capitals)
            else {
                return !isCapitalShip(char);
            }
        }
        
        const name = char.name || char.unit_name || '';
        const baseId = char.base_id || char.id || '';
        return name.toLowerCase().includes(searchTerm) || baseId.toLowerCase().includes(searchTerm);
    });
    
    if (filteredCharacters.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #718096;"><div style="font-size: 1.2rem; margin-bottom: 8px;">🔍</div><div>No characters found</div><div style="font-size: 0.85rem; margin-top: 8px;">Try a different search term</div></div>';
        return;
    }
    
    grid.innerHTML = filteredCharacters.map(char => {
        if (!char) return '';
        
        const charName = char.name || char.unit_name || 'Unknown';
        const baseId = char.base_id || char.id || '';
        const imageUrl = char.image || char.portrait || `https://swgoh.gg/static/img/assets/tex.char_${baseId}.png`;
        const isLeader = selectedLeader && selectedLeader.id === baseId;
        const isMember = selectedMembers.some(c => c.id === baseId);
        
        // Check if character is already used in defense (only for defense teams)
        const usedLocation = currentTeamContext && currentTeamContext.type === 'defense' ? usedDefenseCharacters.get(baseId) : null;
        const isUsedInDefense = usedLocation !== null && usedLocation !== undefined;
        
        // Escape single quotes for onclick
        const safeName = charName.replace(/'/g, "\\'");
        const safeImage = imageUrl.replace(/'/g, "\\'");
        const safeId = baseId.replace(/'/g, "\\'");
        
        let statusClass = '';
        if (isLeader) statusClass = 'selected-leader';
        else if (isMember) statusClass = 'selected-member';
        else if (isUsedInDefense) statusClass = 'used-in-defense';
        
        const clickHandler = isUsedInDefense ? '' : `onclick="toggleCharacterSafe('${safeId}', '${safeName}', '${safeImage}')"`;
        const cursorStyle = isUsedInDefense ? 'cursor: not-allowed;' : '';
        
        // Create location text for badge (just territory name, no team number)
        let locationText = 'Used';
        if (usedLocation) {
            locationText = usedLocation.territory;
        }
        
        return `
            <div class="character-item ${statusClass}" ${clickHandler} style="${cursorStyle}">
                <img src="${imageUrl}" alt="${charName}" onerror="this.src='https://via.placeholder.com/80?text=?'" />
                <div class="character-name">${charName}</div>
                ${isLeader ? '<div class="character-badge leader-badge">Leader</div>' : ''}
                ${isUsedInDefense ? `<div class="character-badge used-badge" title="Used in ${usedLocation.territory}, Team ${usedLocation.team}">${locationText}</div>` : ''}
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
    // Fleet teams: leader must be Capital, members must be regular ships
    if (currentTeamContext && currentTeamContext.type === 'fleet') {
        const isCapital = isCapitalShip(character);
        const isRegularShip = isShip(character) && !isCapital;
        
        // Check if already selected
        const isLeader = selectedLeader && selectedLeader.id === character.base_id;
        const isMember = selectedMembers.some(c => c.id === character.base_id);
        
        if (isLeader) {
            // Remove leader
            selectedLeader = null;
        } else if (isMember) {
            // Remove member
            selectedMembers = selectedMembers.filter(c => c.id !== character.base_id);
        } else {
            // Add new selection
            if (!selectedLeader) {
                // First selection must be a Capital ship
                if (!isCapital) {
                    alert('Please select a Capital ship as the fleet leader first.');
                    return;
                }
                selectedLeader = {
                    id: character.base_id,
                    name: character.name,
                    image: character.image || `https://swgoh.gg/static/img/assets/tex.char_${character.base_id}.png`
                };
            } else {
                // Subsequent selections must be regular ships (not capitals)
                if (isCapital) {
                    alert('Only one Capital ship is allowed per fleet. Please select a regular ship.');
                    return;
                }
                if (!isRegularShip) {
                    alert('Please select a ship (not a character).');
                    return;
                }
                selectedMembers.push({
                    id: character.base_id,
                    name: character.name,
                    image: character.image || `https://swgoh.gg/static/img/assets/tex.char_${character.base_id}.png`
                });
            }
        }
        updateSelectedList();
        displayCharacters();
        return;
    }
    
    // Character teams need leader + members structure
    const format = document.getElementById('format').value;
    const maxMembers = format === '5v5' ? 4 : 2;
    
    // Check if character is already used in defense (only for defense teams)
    if (currentTeamContext && currentTeamContext.type === 'defense') {
        const usedLocation = usedDefenseCharacters.get(character.base_id);
        if (usedLocation) {
            // Check if it's not already in the current selection
            const isInCurrentSelection = (selectedLeader && selectedLeader.id === character.base_id) ||
                                        selectedMembers.some(c => c.id === character.base_id);
            if (!isInCurrentSelection) {
                alert(`${character.name} is already used in ${usedLocation.territory}, Team ${usedLocation.team}. Each character can only be used once in defense.`);
                return;
            }
        }
    }
    
    // Check if it's already the leader
    if (selectedLeader && selectedLeader.id === character.base_id) {
        selectedLeader = null;
    } else if (!selectedLeader) {
        // Set as leader if no leader selected
        selectedLeader = {
            id: character.base_id,
            name: character.name,
            image: character.image || `https://swgoh.gg/static/img/assets/tex.char_${character.base_id}.png`
        };
        // Remove from members if it was there
        selectedMembers = selectedMembers.filter(c => c.id !== character.base_id);
    } else {
        // Toggle as member
        const memberIndex = selectedMembers.findIndex(c => c.id === character.base_id);
        if (memberIndex > -1) {
            selectedMembers.splice(memberIndex, 1);
        } else {
            if (selectedMembers.length >= maxMembers) {
                alert(`Maximum ${maxMembers} members allowed for ${format} format`);
                return;
            }
            selectedMembers.push({
                id: character.base_id,
                name: character.name,
                image: character.image || `https://swgoh.gg/static/img/assets/tex.char_${character.base_id}.png`
            });
        }
    }
    updateSelectedList();
    displayCharacters();
}

function updateSelectedList() {
    const leaderDiv = document.getElementById('selectedLeader');
    const membersDiv = document.getElementById('selectedMembers');
    
    // Fleet teams: show Capital ship as leader, regular ships as members
    if (currentTeamContext && currentTeamContext.type === 'fleet') {
        // Show Capital ship (leader)
        if (selectedLeader) {
            leaderDiv.innerHTML = `
                <div class="selected-character leader-character">
                    <img src="${selectedLeader.image}" alt="${selectedLeader.name}" onerror="this.src='https://via.placeholder.com/40?text=?'" />
                    <span>${selectedLeader.name} (Capital)</span>
                    <button type="button" onclick="removeLeader()">×</button>
                </div>
            `;
        } else {
            leaderDiv.innerHTML = '<span class="empty-state">No Capital ship selected</span>';
        }
        
        // Show regular ships (members)
        if (selectedMembers.length > 0) {
            membersDiv.innerHTML = selectedMembers.map(char => `
                <div class="selected-character">
                    <img src="${char.image}" alt="${char.name}" onerror="this.src='https://via.placeholder.com/40?text=?'" />
                    <span>${char.name}</span>
                    <button type="button" onclick="removeMember('${char.id}')">×</button>
                </div>
            `).join('');
        } else {
            membersDiv.innerHTML = '<span class="empty-state">No ships selected</span>';
        }
        return;
    }
    
    // Character teams need leader + members
    if (selectedLeader) {
        leaderDiv.innerHTML = `
            <div class="selected-character leader-character">
                <img src="${selectedLeader.image}" alt="${selectedLeader.name}" onerror="this.src='https://via.placeholder.com/40?text=?'" />
                <span>${selectedLeader.name}</span>
                <button type="button" onclick="removeLeader()">×</button>
            </div>
        `;
    } else {
        leaderDiv.innerHTML = '<span class="empty-state">No leader selected</span>';
    }
    
    if (selectedMembers.length > 0) {
        membersDiv.innerHTML = selectedMembers.map(char => `
            <div class="selected-character">
                <img src="${char.image}" alt="${char.name}" onerror="this.src='https://via.placeholder.com/40?text=?'" />
                <span>${char.name}</span>
                <button type="button" onclick="removeMember('${char.id}')">×</button>
            </div>
        `).join('');
    } else {
        membersDiv.innerHTML = '<span class="empty-state">No members selected</span>';
    }
    
    updateMemberLimit();
}

function updateMemberLimit() {
    const format = document.getElementById('format').value;
    const maxMembers = format === '5v5' ? 4 : 2;
    const limitDiv = document.getElementById('memberLimit');
    limitDiv.textContent = `${selectedMembers.length} / ${maxMembers} members selected`;
    
    if (selectedMembers.length >= maxMembers) {
        limitDiv.style.color = '#38a169';
        limitDiv.style.fontWeight = '600';
    } else {
        limitDiv.style.color = '#718096';
        limitDiv.style.fontWeight = '400';
    }
}

function removeLeader() {
    selectedLeader = null;
    updateSelectedList();
    displayCharacters();
}

function removeMember(characterId) {
    selectedMembers = selectedMembers.filter(c => c.id !== characterId);
    updateSelectedList();
    displayCharacters();
}

function confirmCharacterSelection() {
    if (!currentTeamContext) return;
    
    const { type, territory, slot } = currentTeamContext;
    
    // Fleet teams: leader (Capital) + members (regular ships)
    if (type === 'fleet') {
        // Validate fleet composition
        if (!selectedLeader) {
            alert('Please select a Capital ship as the fleet leader');
            return;
        }
        
        // Validate that leader is a Capital ship
        const leaderIsCapital = isCapitalShip({ base_id: selectedLeader.id, name: selectedLeader.name });
        if (!leaderIsCapital) {
            alert('The fleet leader must be a Capital ship');
            return;
        }
        
        // Validate that all members are regular ships (not capitals)
        for (let member of selectedMembers) {
            const memberIsCapital = isCapitalShip({ base_id: member.id, name: member.name });
            if (memberIsCapital) {
                alert('Only one Capital ship is allowed per fleet. Please remove the Capital ship from members.');
                return;
            }
            const memberIsShip = isShip({ base_id: member.id, name: member.name });
            if (!memberIsShip) {
                alert('All fleet members must be ships');
                return;
            }
        }
        
        const isDefense = territory === 0; // Top Back defense is index 0
        const displayId = isDefense ? `fleet-defense-${slot}` : `fleet-offense-${slot}`;
        const display = document.getElementById(displayId);
        
        if (display) {
            display.innerHTML = '';
            
            // Display Capital ship (leader) first, then regular ships (members)
            if (selectedLeader) {
                const leaderImg = document.createElement('img');
                leaderImg.className = 'character-image leader';
                leaderImg.src = selectedLeader.image;
                leaderImg.alt = selectedLeader.name;
                leaderImg.dataset.characterId = selectedLeader.id;
                leaderImg.dataset.characterName = selectedLeader.name;
                leaderImg.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                leaderImg.title = selectedLeader.name + ' (Capital) - Click to edit';
                leaderImg.style.cursor = 'pointer';
                leaderImg.onclick = function() {
                    openCharacterModal(type, territory, slot);
                };
                display.appendChild(leaderImg);
            }
            
            selectedMembers.forEach(char => {
                const img = document.createElement('img');
                img.className = 'character-image member';
                img.src = char.image;
                img.alt = char.name;
                img.dataset.characterId = char.id;
                img.dataset.characterName = char.name;
                img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                img.title = char.name + ' - Click to edit';
                img.style.cursor = 'pointer';
                img.onclick = function() {
                    openCharacterModal(type, territory, slot);
                };
                display.appendChild(img);
            });
            
            if (!selectedLeader && selectedMembers.length === 0) {
                display.innerHTML = '<span class="team-select-placeholder">Select Fleet</span>';
            }
        }
    } else {
        // Character teams need leader + members structure
        const format = document.getElementById('format').value;
        const maxMembers = format === '5v5' ? 4 : 2;
        
        // Validate team composition
        if (!selectedLeader) {
            alert('Please select a leader for the team');
            return;
        }
        
        if (selectedMembers.length === 0) {
            alert('Please select at least one member for the team');
            return;
        }
        
        if (selectedMembers.length > maxMembers) {
            alert(`Maximum ${maxMembers} members allowed for ${format} format`);
            return;
        }
        
        const displayId = slot !== null 
            ? `${type}-${territory}-${slot}`
            : `${type}-${territory}`;
        const display = document.getElementById(displayId);
        
        if (display) {
            display.innerHTML = '';
            
            // Display leader first
            if (selectedLeader) {
                const leaderImg = document.createElement('img');
                leaderImg.className = 'character-image leader';
                leaderImg.src = selectedLeader.image;
                leaderImg.alt = selectedLeader.name;
                leaderImg.dataset.characterId = selectedLeader.id;
                leaderImg.dataset.characterName = selectedLeader.name;
                leaderImg.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                leaderImg.title = selectedLeader.name + ' (Leader) - Click to edit';
                leaderImg.style.cursor = 'pointer';
                leaderImg.onclick = function() {
                    openCharacterModal(type, territory, slot);
                };
                display.appendChild(leaderImg);
            }
            
            // Display members
            selectedMembers.forEach(char => {
                const img = document.createElement('img');
                img.className = 'character-image member';
                img.src = char.image;
                img.alt = char.name;
                img.dataset.characterId = char.id;
                img.dataset.characterName = char.name;
                img.onerror = function() { this.src = 'https://via.placeholder.com/50?text=?'; };
                img.title = char.name + ' - Click to edit';
                img.style.cursor = 'pointer';
                img.onclick = function() {
                    openCharacterModal(type, territory, slot);
                };
                display.appendChild(img);
            });
        }
    }
    
    updateCounts();
    closeCharacterModal();
    
    // Auto-save after team selection
    autoSave();
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
window.removeLeader = removeLeader;
window.removeMember = removeMember;
</script>

<?php require_once 'includes/footer.php'; ?>
