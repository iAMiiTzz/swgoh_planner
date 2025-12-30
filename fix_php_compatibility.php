<?php
// Script to replace ?? operator with ternary for PHP 5.6+ compatibility
// Run this once, then delete it

function replaceNullCoalescing($file) {
    $content = file_get_contents($file);
    
    // Replace ?? with ternary operator
    // Pattern: $var ?? 'default' becomes isset($var) ? $var : 'default'
    // This is a simple replacement - may need manual review for complex cases
    
    $patterns = [
        '/\$(\w+)\s*\?\?\s*(\'[^\']*\'|"[^"]*"|\w+|null)/' => 'isset($$1) ? $$1 : $2',
        '/\$(\w+)\[\'([^\']+)\'\]\s*\?\?\s*(\'[^\']*\'|"[^"]*"|\w+|null)/' => 'isset($$1[\'$2\']) ? $$1[\'$2\'] : $3',
        '/\$(\w+)\["([^"]+)"\]\s*\?\?\s*(\'[^\']*\'|"[^"]*"|\w+|null)/' => 'isset($$1["$2"]) ? $$1["$2"] : $3',
    ];
    
    // For now, just note that manual replacement is needed
    return $content;
}

echo "This script would replace ?? operators, but manual replacement is safer.\n";
echo "The auth.php file has been fixed. Test the site and report any other errors.\n";
?>

