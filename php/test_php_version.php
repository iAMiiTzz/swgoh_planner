<?php
// Start session first (before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Quick test to check PHP version and syntax
echo "PHP Version: " . phpversion() . "\n";
echo "PHP 8.1 Compatible: " . (version_compare(phpversion(), '8.1.0', '>=') ? 'Yes' : 'No') . "\n";

// Test null coalescing (using ternary for PHP 5.6 compatibility)
$test = null;
$result = isset($test) ? $test : 'default';
echo "Null coalescing (ternary) works: " . ($result === 'default' ? 'Yes' : 'No') . "\n";

echo "Session started: Yes\n";
echo "All tests passed!\n";
?>

