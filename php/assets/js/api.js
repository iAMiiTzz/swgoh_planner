// API utility for making requests
const API_BASE = 'api';

async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE}/${endpoint}`;
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    const config = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {}),
        },
    };
    
    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// API methods
const api = {
    get: (endpoint) => apiRequest(endpoint, { method: 'GET' }),
    post: (endpoint, data) => apiRequest(endpoint, { method: 'POST', body: data }),
    put: (endpoint, data) => apiRequest(endpoint, { method: 'PUT', body: data }),
    delete: (endpoint) => apiRequest(endpoint, { method: 'DELETE' }),
};

// Auth API
api.auth = {
    login: (username, password) => api.post('auth.php?action=login', { username, password }),
    verify: () => api.get('auth.php?action=verify'),
    changePassword: (currentPassword, newPassword) => api.post('auth.php?action=change-password', { currentPassword, newPassword }),
    changeUsername: (newUsername, password) => api.post('auth.php?action=change-username', { newUsername, password }),
    updateAllyCodes: (codes) => api.post('auth.php?action=update-ally-codes', codes),
    logout: () => api.post('auth.php?action=logout'),
};

// GAC API
api.gac = {
    getAll: () => api.get('gac.php'),
    get: (id) => api.get(`gac.php?id=${id}`),
    create: (data) => api.post('gac.php', data),
    update: (id, data) => api.put(`gac.php?id=${id}`, data),
    delete: (id) => api.delete(`gac.php?id=${id}`),
};

// Journey API
api.journey = {
    getAll: () => api.get('journey.php'),
    get: (id) => api.get(`journey.php?id=${id}`),
    create: (data) => api.post('journey.php', data),
    update: (id, data) => api.put(`journey.php?id=${id}`, data),
    delete: (id) => api.delete(`journey.php?id=${id}`),
};

// Roster API
api.roster = {
    getAll: () => api.get('roster.php'),
    get: (id) => api.get(`roster.php?id=${id}`),
    create: (data) => api.post('roster.php', data),
    update: (id, data) => api.put(`roster.php?id=${id}`, data),
    delete: (id) => api.delete(`roster.php?id=${id}`),
};

// Gear API
api.gear = {
    getAll: () => api.get('gear.php'),
    get: (id) => api.get(`gear.php?id=${id}`),
    create: (data) => api.post('gear.php', data),
    update: (id, data) => api.put(`gear.php?id=${id}`, data),
    delete: (id) => api.delete(`gear.php?id=${id}`),
};

// Guild API
api.guild = {
    getUsers: () => api.get('guild.php'),
};

// Admin API
api.admin = {
    getUsers: () => api.get('admin.php?action=users'),
    createUser: (data) => api.post('admin.php?action=users', data),
    deleteUser: (id) => api.delete(`admin.php?action=users&id=${id}`),
};

// SWGOH API
api.swgoh = {
    getUnits: () => api.get('swgoh.php?action=units'),
    getUnit: (id) => api.get(`swgoh.php?action=units&id=${id}`),
};

