<?php
session_start();

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Get current user ID
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user role
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
}

// Check if user is admin
function isAdmin() {
    return getUserRole() === 'admin';
}

// Require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
}

// Require admin role
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
}

// Set JSON response header
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
