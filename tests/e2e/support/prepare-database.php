<?php

/**
 * Builds the isolated database the Playwright suite runs against.
 *
 * Run with DB_DATABASE set to a name ending in "_e2e" (playwright.config.ts
 * does this). Creates the database if needed, then migrate:fresh + E2eSeeder.
 * Anything else is refused before a single statement runs, so the real
 * database can never be wiped by this script.
 */

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../../vendor/autoload.php';

$database = getenv('DB_DATABASE') ?: '';

if (! str_ends_with($database, '_e2e')) {
    fwrite(STDERR, "Refusing to prepare [{$database}]: DB_DATABASE must end in _e2e.\n");
    exit(1);
}

$app = require __DIR__.'/../../../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$connection = config('database.connections.'.config('database.default'));

if (config('database.connections.'.config('database.default').'.database') !== $database) {
    fwrite(STDERR, "Refusing: the resolved database does not match DB_DATABASE.\n");
    exit(1);
}

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s', $connection['host'], $connection['port']),
    $connection['username'],
    $connection['password'],
);
$pdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $database));

$status = $kernel->call('migrate:fresh', [
    '--seed' => true,
    '--seeder' => 'Database\\Seeders\\E2eSeeder',
    '--force' => true,
]);

echo $kernel->output();

exit($status);
