<?php
/**
 * Fix Czech character encoding in database
 * This script converts incorrectly stored UTF-8 characters back to proper UTF-8
 */

require __DIR__ . '/public/ims-dashboard/define.php';

echo "Starting Czech character encoding fix...\n\n";

// Set proper connection charset
$mysqli->set_charset('utf8mb4');

// Fix products table
echo "Fixing products table...\n";
$result = $mysqli->query("SELECT id, name FROM products WHERE name LIKE '%??%'");
$fixed = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $originalName = $row['name'];
        
        // Convert from latin1 to UTF-8 (fix double encoding)
        $fixedName = mb_convert_encoding($originalName, 'UTF-8', 'Windows-1252');
        
        // If that doesn't work, try direct UTF-8 fix
        if (strpos($fixedName, '?') !== false) {
            $fixedName = iconv('ISO-8859-1', 'UTF-8', $originalName);
        }
        
        if ($fixedName !== $originalName) {
            $stmt = $mysqli->prepare("UPDATE products SET name = ? WHERE id = ?");
            $stmt->bind_param('si', $fixedName, $row['id']);
            
            if ($stmt->execute()) {
                echo "✓ Fixed ID {$row['id']}: {$originalName} → {$fixedName}\n";
                $fixed++;
            }
            $stmt->close();
        }
    }
    $result->free();
}

echo "\n✅ Fixed {$fixed} products\n";
echo "Done!\n";

$mysqli->close();
