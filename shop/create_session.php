<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

function checkout_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function checkout_limit_text(string $value, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length);
    }

    return substr($value, 0, $length);
}

function checkout_base_url(): string
{
    if (defined('CHECKOUT_BASE_URL') && is_string(CHECKOUT_BASE_URL) && CHECKOUT_BASE_URL !== '') {
        return rtrim(CHECKOUT_BASE_URL, '/');
    }

    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/shop/create_session.php';
    $path = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $scheme . '://' . $host . ($path === '' ? '' : $path);
}

function checkout_load_stripe_autoload(): void
{
    $candidates = [
        __DIR__ . '/vendor/autoload.php',
        dirname(__DIR__) . '/vendor/autoload.php',
        dirname(__DIR__, 2) . '/vendor/autoload.php',
        dirname(__DIR__, 3) . '/vendor/autoload.php',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            require_once $candidate;
            return;
        }
    }
}

function checkout_create_session(array $payload): array
{
    checkout_load_stripe_autoload();

    if (class_exists('\\Stripe\\StripeClient')) {
        $stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
        $session = $stripe->checkout->sessions->create($payload);
        return method_exists($session, 'toArray') ? $session->toArray() : (array) $session;
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL extension is not available.');
    }

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Stripe-Version: 2026-02-25.clover',
        ],
        CURLOPT_POSTFIELDS => http_build_query($payload, '', '&', PHP_QUERY_RFC3986),
    ]);

    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false || $body === '') {
        throw new RuntimeException('Stripe API request failed. ' . $error);
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Stripe API response could not be parsed.');
    }

    if ($status >= 400) {
        $message = $decoded['error']['message'] ?? 'Stripe API returned an error.';
        throw new RuntimeException($message);
    }

    return $decoded;
}

function checkout_cart_items(array $products): array
{
    $cart = is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
    $items = [];

    foreach ($cart as $id => $quantity) {
        $id = (string) $id;
        $quantity = (int) $quantity;
        if ($quantity < 1 || !isset($products[$id])) {
            continue;
        }
        $product = $products[$id];
        $items[] = [
            'id' => $id,
            'product' => $product,
            'quantity' => min(99, $quantity),
            'subtotal' => (int) $product['amount'] * min(99, $quantity),
        ];
    }

    return $items;
}

function checkout_orders_dir(): string
{
    return __DIR__ . '/data';
}

function checkout_orders_path(): string
{
    return checkout_orders_dir() . '/orders.json';
}

function checkout_generate_order_id(): string
{
    return 'SN' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function checkout_ensure_orders_storage(): void
{
    $dir = checkout_orders_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Order data directory could not be created.');
    }

    $htaccess = $dir . '/.htaccess';
    if (!is_file($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n", LOCK_EX);
    }

    $path = checkout_orders_path();
    if (!is_file($path)) {
        file_put_contents($path, "[]\n", LOCK_EX);
    }
}

function checkout_save_order_record(array $record): void
{
    checkout_ensure_orders_storage();

    $path = checkout_orders_path();
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Order history could not be opened.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Order history could not be locked.');
        }

        $contents = stream_get_contents($handle);
        $orders = [];
        if (is_string($contents) && trim($contents) !== '') {
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $orders = array_is_list($decoded) ? $decoded : array_values($decoded);
            }
        }

        $orders[] = $record;
        $json = json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new RuntimeException('Order history could not be encoded.');
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $json . PHP_EOL);
        fflush($handle);
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function checkout_error_page(string $message): void
{
    http_response_code(400);
    echo '<!doctype html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>決済エラー</title><style>body{font-family:Inter,&quot;Noto Sans JP&quot;,sans-serif;background:#f6f5f4;color:#37352f;margin:0;padding:40px}.card{max-width:720px;margin:auto;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:28px}.button{display:inline-block;margin-top:16px;background:#0075de;color:#fff;text-decoration:none;border-radius:4px;padding:10px 14px}</style></head><body><main class="card"><h1>決済画面を作成できませんでした</h1><p>' . checkout_h($message) . '</p><a class="button" href="cart.php">カートへ戻る</a></main></body></html>';
}

try {
    checkout_assert_configured();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: cart.php', true, 303);
        exit;
    }

    $customerName = trim((string) ($_POST['customer_name'] ?? ''));
    $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));
    $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
    $customerReference = trim((string) ($_POST['customer_reference'] ?? ''));
    $customerConfirm = (string) ($_POST['customer_confirm'] ?? '') === '1';

    if ($customerName === '') {
        throw new InvalidArgumentException('氏名を入力してください。');
    }

    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('メールアドレスを正しく入力してください。');
    }

    if ($customerPhone === '') {
        throw new InvalidArgumentException('電話番号を入力してください。');
    }

    if ($customerReference === '') {
        throw new InvalidArgumentException('既存顧客・取引先確認情報を入力してください。初めてのお客様は、購入前にお問い合わせフォームよりご相談ください。');
    }

    if (!$customerConfirm) {
        throw new InvalidArgumentException('既存顧客・取引先であることの確認に同意してください。初めてのお客様は、購入前にお問い合わせフォームよりご相談ください。');
    }

    $products = checkout_products();
    $items = checkout_cart_items($products);
    if ($items === []) {
        throw new InvalidArgumentException('カートに商品がありません。');
    }

    $lineItems = [];
    $total = 0;
    $summaryParts = [];

    foreach ($items as $item) {
        $product = $item['product'];
        if (!checkout_is_product_available($product)) {
            throw new InvalidArgumentException('在庫切れまたは近日入荷の商品が含まれています。カートを確認してください: ' . (string) $product['name']);
        }

        $quantity = (int) $item['quantity'];
        $amount = (int) $product['amount'];
        $total += (int) $item['subtotal'];
        $summaryParts[] = $product['name'] . ' x ' . $quantity;

        $lineItems[] = [
            'quantity' => $quantity,
            'price_data' => [
                'currency' => STRIPE_CURRENCY,
                'unit_amount' => $amount,
                'product_data' => [
                    'name' => (string) $product['name'],
                    'description' => checkout_limit_text((string) $product['description'], 400),
                ],
            ],
        ];
    }

    $baseUrl = checkout_base_url();
    $summary = checkout_limit_text(implode(' / ', $summaryParts), 480);
    $orderId = checkout_generate_order_id();

    $payload = [
        'mode' => 'payment',
        'payment_method_types' => checkout_payment_method_types($total),
        'success_url' => $baseUrl . '/complete.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $baseUrl . '/cart.php',
        'customer_email' => $customerEmail,
        'line_items' => $lineItems,
        'metadata' => [
            'order_id' => $orderId,
            'customer_name' => checkout_limit_text($customerName, 120),
            'customer_phone' => checkout_limit_text($customerPhone, 120),
            'customer_reference' => checkout_limit_text($customerReference, 180),
            'member_purchase_confirmed' => 'yes',
            'items_summary' => $summary,
            'item_count' => (string) array_sum(array_map(static fn(array $item): int => (int) $item['quantity'], $items)),
            'cart_total' => (string) $total,
        ],
    ];

    $session = checkout_create_session($payload);
    if (empty($session['url'])) {
        throw new RuntimeException('Stripe Checkout URL was not returned.');
    }

    checkout_save_order_record([
        'order_id' => $orderId,
        'created_at' => date(DATE_ATOM),
        'stripe_session_id' => (string) ($session['id'] ?? ''),
        'stripe_payment_intent' => (string) ($session['payment_intent'] ?? ''),
        'checkout_status' => (string) ($session['status'] ?? 'created'),
        'payment_status' => (string) ($session['payment_status'] ?? 'created'),
        'customer' => [
            'name' => checkout_limit_text($customerName, 120),
            'email' => checkout_limit_text($customerEmail, 180),
            'phone' => checkout_limit_text($customerPhone, 120),
            'reference' => checkout_limit_text($customerReference, 180),
        ],
        'items' => array_map(static function (array $item): array {
            $product = $item['product'];
            return [
                'id' => (string) $item['id'],
                'name' => (string) $product['name'],
                'quantity' => (int) $item['quantity'],
                'unit_amount' => (int) $product['amount'],
                'subtotal' => (int) $item['subtotal'],
            ];
        }, $items),
        'total' => $total,
        'currency' => STRIPE_CURRENCY,
        'payment_method_types' => $session['payment_method_types'] ?? $payload['payment_method_types'],
        'member_purchase_confirmed' => true,
        'notes' => '',
    ]);

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'application/json') !== false) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'id' => $session['id'] ?? '',
            'url' => $session['url'],
            'total' => $total,
            'line_items_count' => count($lineItems),
            'payment_method_types' => $session['payment_method_types'] ?? $payload['payment_method_types'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Location: ' . $session['url'], true, 303);
    exit;
} catch (Throwable $e) {
    checkout_error_page($e->getMessage());
}
