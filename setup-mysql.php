<?php

/**
 * Script to update .env file for MySQL configuration
 * Run: php setup-mysql.php
 */

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    echo "Error: .env file not found. Please copy .env.example to .env first.\n";
    exit(1);
}

$envContent = file_get_contents($envPath);

// Update database configuration
$envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
$envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=localhost', $envContent);
$envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=3306', $envContent);
$envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=dashboard', $envContent);
$envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=root', $envContent);
$envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=', $envContent);

file_put_contents($envPath, $envContent);

echo "✓ .env file updated successfully!\n";
echo "Database configuration:\n";
echo "  DB_CONNECTION=mysql\n";
echo "  DB_HOST=localhost\n";
echo "  DB_PORT=3306\n";
echo "  DB_DATABASE=dashboard\n";
echo "  DB_USERNAME=root\n";
echo "  DB_PASSWORD=\n";
echo "\n";
echo "Next steps:\n";
echo "1. Make sure MySQL is running and the 'dashboard' database exists\n";
echo "2. Run: php artisan migrate\n";
echo "3. Run: php artisan db:seed --class=TestUsersSeeder\n";

