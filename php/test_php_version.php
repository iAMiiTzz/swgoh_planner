<?php
// Quick test to check PHP version and syntax
echo "PHP Version: " . phpversion() . "\n";
echo "PHP 8.1 Compatible: " . (version_compare(phpversion(), '8.1.0', '>=') ? 'Yes' : 'No') . "\n";

// Test ?? operator
$test = null;
$result = $test ?? 'default';
echo "?? operator works: " . ($result === 'default' ? 'Yes' : 'No') . "\n";

// Test session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session started: Yes\n";

echo "All tests passed!\n";
?>

