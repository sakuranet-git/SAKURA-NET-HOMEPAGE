<?php
declare(strict_types=1);

header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex');

$configPath = __DIR__ . '/contact_security_config.php';
if (is_file($configPath)) {
    require $configPath;
}

$siteKey = defined('CONTACT_TURNSTILE_SITE_KEY') ? (string) CONTACT_TURNSTILE_SITE_KEY : '';

echo 'window.SAKURA_TURNSTILE_SITE_KEY = ' . json_encode($siteKey, JSON_UNESCAPED_SLASHES) . ';';
