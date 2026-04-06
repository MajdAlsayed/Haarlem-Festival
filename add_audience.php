<?php
require 'app/vendor/autoload.php';

try {
    $db = new PDO('mysql:host=mysql;dbname=HaarlemFestivaldb;charset=utf8mb4', 'root', '');
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM stories LIKE 'audience'");
    if ($stmt->rowCount() === 0) {
        // Column doesn't exist, add it
        $db->exec("ALTER TABLE stories ADD COLUMN audience VARCHAR(50) DEFAULT '' NOT NULL AFTER template");
        echo "✓ Audience column added successfully\n";
    } else {
        echo "✓ Audience column already exists\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
