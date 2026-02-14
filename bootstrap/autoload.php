<?php

/**
 * Flashcards tenant autoloader
 *
 * Registers tenant-specific namespaces. Add new prefixes here when adding
 * new feature modules (e.g. App\Features\Flashcards\).
 */

$tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
if (!$tenantId) {
    return;
}

$appPath = __DIR__ . '/../app';
if (!is_dir($appPath)) {
    return;
}

$prefixes = [
    'App\\Features\\Auth\\',
    'App\\Features\\Flashcards\\',
];

$autoloaderKey = '__tenant_autoload_' . $tenantId;
if (isset($GLOBALS[$autoloaderKey])) {
    return;
}

spl_autoload_register(function ($class) use ($appPath, $prefixes) {
    $matched = false;
    foreach ($prefixes as $prefix) {
        if (strpos($class, $prefix) === 0) {
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        return false;
    }

    $relativePath = str_replace('App\\', '', $class);
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
    $filePath = $appPath . DIRECTORY_SEPARATOR . $relativePath . '.php';
    $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

    if (file_exists($filePath)) {
        require $filePath;
        return true;
    }

    return false;
}, true, false);

$GLOBALS[$autoloaderKey] = true;
