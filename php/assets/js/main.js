// Main JavaScript utilities

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        api.auth.logout()
            .then(() => {
                window.location.href = 'login.php';
            })
            .catch(() => {
                window.location.href = 'login.php';
            });
    }
}

// Format ally code
function formatAllyCode(code) {
    if (!code) return '';
    const cleaned = code.replace(/-/g, '');
    if (cleaned.length === 9) {
        return cleaned.slice(0, 3) + '-' + cleaned.slice(3, 6) + '-' + cleaned.slice(6);
    }
    return cleaned;
}

// Show error message
function showError(message) {
    alert(message);
}

// Show success message
function showSuccess(message) {
    alert(message);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

