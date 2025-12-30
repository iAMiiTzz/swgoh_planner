<?php
// Clear OPcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared successfully<br>";
} else {
    echo "ℹ OPcache is not enabled<br>";
}

// Test ?? operator (using ternary for compatibility)
$test = null;
$result = isset($test) ? $test : 'default';
echo "✓ Null coalescing test: " . ($result === 'default' ? 'PASSED' : 'FAILED') . "<br>";

// Test PHP version
echo "✓ PHP Version: " . phpversion() . "<br>";

// Test if auth.php can be included
echo "<br>Testing config/auth.php inclusion:<br>";
try {
    require_once 'config/auth.php';
    echo "✓ config/auth.php loaded successfully<br>";
    
    // Test the functions
    if (function_exists('getUserId')) {
        echo "✓ getUserId() function exists<br>";
    } else {
        echo "✗ getUserId() function NOT found<br>";
    }
    
    if (function_exists('getUserRole')) {
        echo "✓ getUserRole() function exists<br>";
    } else {
        echo "✗ getUserRole() function NOT found<br>";
    }
    
} catch (ParseError $e) {
    echo "✗ Parse Error: " . $e->getMessage() . "<br>";
    echo "✗ File: " . $e->getFile() . "<br>";
    echo "✗ Line: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<br><strong>Diagnostics complete. You can delete this file after checking.</strong>";
?>

