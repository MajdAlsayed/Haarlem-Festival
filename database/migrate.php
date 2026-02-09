<?php
/**
 * Database Migration Script
 * Applies pending SQL migrations to the database
 */

// Autoload classes
require_once __DIR__ . '/../app/vendor/autoload.php';

use App\Core\Database;

// Color output for terminal
function colorOutput($text, $color = 'green') {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];

    return $colors[$color] . $text . $colors['reset'];
}

try {
    echo "=== Database Migration Tool ===\n\n";

    // Get database connection
    $pdo = Database::getConnection();
    echo colorOutput("✓ Connected to database\n", 'green');

    // Create schema_migrations table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(255) UNIQUE NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo colorOutput("✓ Schema migrations table ready\n", 'green');

    // Get already applied migrations
    $stmt = $pdo->query("SELECT version FROM schema_migrations ORDER BY version");
    $appliedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "\nApplied migrations: " . count($appliedMigrations) . "\n";
    if (!empty($appliedMigrations)) {
        foreach ($appliedMigrations as $migration) {
            echo colorOutput("  ⏭  $migration\n", 'blue');
        }
    }

    // Get all migration files
    $migrationsPath = __DIR__ . '/migrations';
    if (!is_dir($migrationsPath)) {
        mkdir($migrationsPath, 0755, true);
        echo colorOutput("\n✓ Created migrations directory\n", 'green');
    }

    $migrationFiles = glob($migrationsPath . '/*.sql');

    if (empty($migrationFiles)) {
        echo colorOutput("\n⚠ No migration files found in /database/migrations/\n", 'yellow');
        echo "Create your first migration file, for example:\n";
        echo "  001_create_users.sql\n\n";
        exit(0);
    }

    // Sort files by name
    sort($migrationFiles);

    // Find pending migrations
    $pendingMigrations = [];
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        $version = pathinfo($filename, PATHINFO_FILENAME);

        if (!in_array($version, $appliedMigrations)) {
            $pendingMigrations[] = [
                'version' => $version,
                'file' => $file
            ];
        }
    }

    if (empty($pendingMigrations)) {
        echo colorOutput("\n✓ All migrations are up to date!\n", 'green');
        echo "No pending migrations to apply.\n\n";
        exit(0);
    }

    // Apply pending migrations
    echo "\n" . colorOutput("Pending migrations: " . count($pendingMigrations) . "\n", 'yellow');

    foreach ($pendingMigrations as $migration) {
        echo "\nApplying: " . colorOutput($migration['version'], 'yellow') . "\n";

        // Read SQL file
        $sql = file_get_contents($migration['file']);

        if (empty(trim($sql))) {
            echo colorOutput("  ⚠ Warning: Migration file is empty, skipping\n", 'yellow');
            continue;
        }

        try {
            // Execute SQL
            // Note: DDL statements (CREATE, ALTER, DROP) auto-commit in MySQL
            // so we don't use transactions here
            $pdo->exec($sql);

            // Record migration
            $stmt = $pdo->prepare("INSERT INTO schema_migrations (version) VALUES (?)");
            $stmt->execute([$migration['version']]);

            echo colorOutput("  ✓ Successfully applied: {$migration['version']}\n", 'green');

        } catch (Exception $e) {
            echo colorOutput("\n  ✗ ERROR in migration: {$migration['version']}\n", 'red');
            echo colorOutput("  Error message: " . $e->getMessage() . "\n", 'red');
            echo "\nMigration stopped. Please fix the error and run again.\n\n";
            exit(1);
        }
    }

    echo "\n" . colorOutput("=== All migrations applied successfully! ===\n\n", 'green');

} catch (PDOException $e) {
    echo colorOutput("\n✗ Database connection error:\n", 'red');
    echo colorOutput($e->getMessage() . "\n\n", 'red');
    exit(1);
} catch (Exception $e) {
    echo colorOutput("\n✗ Unexpected error:\n", 'red');
    echo colorOutput($e->getMessage() . "\n\n", 'red');
    exit(1);
}