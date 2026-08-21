<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex');

$configPath = __DIR__ . '/contact_security_config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

require $configPath;

$secret = defined('CONTACT_FORM_HMAC_SECRET') ? (string) CONTACT_FORM_HMAC_SECRET : '';
if ($secret === '') {
    http_response_code(500);
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

$random = bin2hex(random_bytes(16));
$issuedAt = time();
$token = $issuedAt . ':' . $random;
$signature = hash_hmac('sha256', $token, $secret);

echo json_encode([
    'ok' => true,
    'token' => $token,
    'signature' => $signature,
    'issued_at' => $issuedAt,
], JSON_UNESCAPED_UNICODE);
