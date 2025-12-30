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
                    <div class="team-slot" data-slot="${i}">
                        <input type="text" class="team-input" placeholder="Team ${i + 1}" data-territory="${index}" data-slot="${i}">
                    </div>
                `).join('')}
            </div>
        `;
        container.appendChild(territoryDiv);
    });
    
    // Add event listeners
    container.querySelectorAll('.team-input').forEach(input => {
        input.addEventListener('change', updateCounts);
    });
}

function generateOffenseTeams(maxTeams) {
    const container = document.getElementById('offenseTeams');
    container.innerHTML = '';
    
    for (let i = 0; i < maxTeams; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-slot';
        teamDiv.innerHTML = `
            <input type="text" class="team-input" placeholder="Offense Team ${i + 1}" data-offense="${i}">
        `;
        container.appendChild(teamDiv);
    }
    
    container.querySelectorAll('.team-input').forEach(input => {
        input.addEventListener('change', updateCounts);
    });
}

function generateFleetTeams(maxTeams) {
    const container = document.getElementById('fleetTeams');
    container.innerHTML = '';
    
    for (let i = 0; i < maxTeams; i++) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'team-slot';
        teamDiv.innerHTML = `
            <input type="text" class="team-input" placeholder="Fleet Team ${i + 1}" data-fleet="${i}">
        `;
        container.appendChild(teamDiv);
    }
    
    container.querySelectorAll('.team-input').forEach(input => {
        input.addEventListener('change', updateCounts);
    });
}

function updateCounts() {
    const league = document.getElementById('league').value;
    const format = document.getElementById('format').value;
    const config = GAC_CONFIG[league][format];
    
    // Count defense teams
    let defenseCount = 0;
    document.querySelectorAll('#defenseTerritories .team-input').forEach(input => {
        if (input.value.trim()) defenseCount++;
    });
    
    // Count offense teams
    let offenseCount = 0;
    document.querySelectorAll('#offenseTeams .team-input').forEach(input => {
        if (input.value.trim()) offenseCount++;
    });
    
    // Count fleet teams
    let fleetCount = 0;
    document.querySelectorAll('#fleetTeams .team-input').forEach(input => {
        if (input.value.trim()) fleetCount++;
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
        document.querySelectorAll(`#defenseTerritories .team-input[data-territory="${tIndex}"]`).forEach(input => {
            if (input.value.trim()) {
                teams.push(input.value.trim());
            }
        });
        defenseTeams.push({
            territory: territory.name,
            teams: teams
        });
    });
    
    // Collect offense teams
    const offenseTeams = [];
    document.querySelectorAll('#offenseTeams .team-input').forEach(input => {
        if (input.value.trim()) {
            offenseTeams.push(input.value.trim());
        }
    });
    
    // Collect fleet teams
    const fleetTeams = [];
    document.querySelectorAll('#fleetTeams .team-input').forEach(input => {
        if (input.value.trim()) {
            fleetTeams.push(input.value.trim());
        }
    });
    
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
                    const input = document.querySelector(`#defenseTerritories .team-input[data-territory="${tIndex}"][data-slot="${teamIndex}"]`);
                    if (input) input.value = team;
                });
            }
        });
    }
    
    // Load offense teams
    if (plan.offense_teams && Array.isArray(plan.offense_teams)) {
        plan.offense_teams.forEach((team, index) => {
            const input = document.querySelector(`#offenseTeams .team-input[data-offense="${index}"]`);
            if (input) input.value = team;
        });
    }
    
    // Load fleet teams
    if (plan.fleet_teams && Array.isArray(plan.fleet_teams)) {
        plan.fleet_teams.forEach((team, index) => {
            const input = document.querySelector(`#fleetTeams .team-input[data-fleet="${index}"]`);
            if (input) input.value = team;
        });
    }
    
    updateCounts();
}
</script>

<?php require_once 'includes/footer.php'; ?>
