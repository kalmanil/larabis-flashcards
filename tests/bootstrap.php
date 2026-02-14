<?php

/**
 * Bootstrap file for Flashcards tenant tests
 * 
 * Sets up tenant context and loads Larabis application
 */

// Set tenant environment variables (same as domain resolver)
$_ENV['DOMAIN_TENANT_ID'] = 'flashcards';
$_ENV['DOMAIN_CODE'] = 'default';
$_ENV['DOMAIN_VIEW_TYPE'] = 'default';
$_ENV['DOMAIN_SITE_TITLE'] = 'Flashcards - Test';

// Also set in putenv for compatibility
putenv('DOMAIN_TENANT_ID=flashcards');
putenv('DOMAIN_CODE=default');
putenv('DOMAIN_VIEW_TYPE=default');
putenv('DOMAIN_SITE_TITLE=Flashcards - Test');

// Path to Larabis root (go up: tests -> flashcards -> tenants -> larabis)
$larabisPath = dirname(__DIR__, 3);

// Verify Larabis path exists
if (!file_exists($larabisPath . '/vendor/autoload.php')) {
    throw new RuntimeException(
        'Larabis vendor/autoload.php not found at: ' . $larabisPath . '/vendor/autoload.php' . PHP_EOL .
        'Make sure you run composer install in the Larabis root directory.'
    );
}

// Load Composer autoloader from Larabis
require $larabisPath . '/vendor/autoload.php';

// Register Tests namespace autoloader FIRST (prepend=true so it runs before Composer's autoloader)
// This ensures our tenant's TestCase is used instead of Larabis's TestCase
spl_autoload_register(function ($class) {
    $prefix = 'Tests\\';
    $baseDir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
}, true, true); // throw=true, prepend=true

// Register tenant-specific autoloader for App\Features\Auth and App\Features\Flashcards (also prepend)
foreach (['App\\Features\\Auth\\' => 'app/Features/Auth/', 'App\\Features\\Flashcards\\' => 'app/Features/Flashcards/'] as $prefix => $baseDir) {
    spl_autoload_register(function ($class) use ($prefix, $baseDir) {
        $baseDir = dirname(__DIR__) . '/' . $baseDir;
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }, true, true);
}
