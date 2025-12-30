<?php
// Get base URL dynamically
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
    
    // Remove port if present
    $host = preg_replace('/:\d+$/', '', $host);
    
    return $protocol . '://' . $host;
}

// Helper function for redirects
function redirect($path) {
    $baseUrl = getBaseUrl();
    $url = $baseUrl . $path;
    header('Location: ' . $url);
    exit;
}
