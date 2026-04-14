<?php

/*
|--------------------------------------------------------------------------
| Flashcards tenant — route loader
|--------------------------------------------------------------------------
|
| Loads exactly one view-specific route file. DOMAIN_CODE must match the
| TenantView code (e.g. default, admin), typically set before Laravel boots
| (per-vhost index.php + config.php).
|
*/

$code = (string) ($_ENV['DOMAIN_CODE'] ?? config('domain.code') ?? 'default');
$code = preg_replace('/[^a-z0-9_-]/', '', $code);
if ($code === '') {
    $code = 'default';
}

$path = __DIR__.'/views/'.$code.'/web.php';

if (!is_file($path)) {
    throw new RuntimeException(
        'No route file for tenant view "'.$code.'". Expected: '.$path
    );
}

require $path;
