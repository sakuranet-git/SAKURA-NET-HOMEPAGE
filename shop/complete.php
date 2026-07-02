<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function complete_load_stripe_autoload(): void
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

function complete_retrieve_session(string $sessionId): array
{
    checkout_assert_configured();
    complete_load_stripe_autoload();

    if (class_exists('\\Stripe\\StripeClient')) {
        $stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => [
                'payment_intent',
                'payment_intent.latest_charge',
                'payment_intent.payment_method',
            ],
        ]);
        return method_exists($session, 'toArray') ? $session->toArray() : (array) $session;
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL extension is not available.');
    }

    $url = 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId) . '?' . http_build_query([
        'expand' => [
            'payment_intent',
            'payment_intent.latest_charge',
            'payment_intent.payment_method',
        ],
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER => ['Stripe-Version: 2026-02-25.clover'],
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

function collect_konbini_codes(mixed $value, array &$codes, string $prefix = ''): void
{
    if (!is_array($value)) {
        return;
    }

    $targetKeys = [
        'confirmation_number',
        'payment_code',
        'receipt_number',
        'payment_number',
        'customer_number',
        'online_payment_number',
        'company_code',
    ];

    foreach ($value as $key => $item) {
        $keyText = is_string($key) ? $key : (string) $key;
        $label = $prefix === '' ? $keyText : $prefix . ' / ' . $keyText;

        if (in_array($keyText, $targetKeys, true) && (is_string($item) || is_numeric($item))) {
            $codes[] = [
                'label' => str_replace('_', ' ', $label),
                'value' => (string) $item,
            ];
            continue;
        }

        if (is_array($item)) {
            collect_konbini_codes($item, $codes, $label);
        }
    }
}

function find_first_string_by_key(mixed $value, array $targetKeys): string
{
    if (!is_array($value)) {
        return '';
    }

    foreach ($value as $key => $item) {
        if (is_string($key) && in_array($key, $targetKeys, true) && is_string($item) && $item !== '') {
            return $item;
        }

        if (is_array($item)) {
            $found = find_first_string_by_key($item, $targetKeys);
            if ($found !== '') {
                return $found;
            }
        }
    }

    return '';
}

function is_konbini_session(array $session, array $paymentIntent): bool
{
    $sessionTypes = $session['payment_method_types'] ?? [];
    $intentTypes = $paymentIntent['payment_method_types'] ?? [];
    $paymentMethod = $paymentIntent['payment_method'] ?? null;
    $charge = is_array($paymentIntent['latest_charge'] ?? null) ? $paymentIntent['latest_charge'] : [];

    if (is_array($paymentMethod) && ($paymentMethod['type'] ?? '') === 'konbini') {
        return true;
    }

    if (($charge['payment_method_details']['type'] ?? '') === 'konbini') {
        return true;
    }

    return in_array('konbini', is_array($intentTypes) ? $intentTypes : [], true)
        || in_array('konbini', is_array($sessionTypes) ? $sessionTypes : [], true);
}

$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$errorMessage = '';
$session = [];
$codes = [];
$voucherUrl = '';
$receiptUrl = '';
$instructionUrl = '';
$instructionUrlLabel = '';
$copyButtonLabel = '受付番号をコピー';
$isKonbini = false;
$expiresAt = null;
$displayText = '';

try {
    if ($sessionId === '') {
        throw new InvalidArgumentException('セッションIDがありません。');
    }

    $session = complete_retrieve_session($sessionId);
    $paymentIntent = is_array($session['payment_intent'] ?? null) ? $session['payment_intent'] : [];
    $latestCharge = is_array($paymentIntent['latest_charge'] ?? null) ? $paymentIntent['latest_charge'] : [];
    $isKonbini = is_konbini_session($session, $paymentIntent);
    $details = $paymentIntent['next_action']['konbini_display_details'] ?? [];

    if (is_array($details)) {
        $voucherUrl = (string) ($details['hosted_voucher_url'] ?? '');
        $expiresAt = $details['expires_at'] ?? null;
        collect_konbini_codes($details, $codes);
    }

    if ($voucherUrl === '') {
        $voucherUrl = find_first_string_by_key($paymentIntent, ['hosted_voucher_url']);
    }

    $receiptUrl = is_string($latestCharge['receipt_url'] ?? null) ? (string) $latestCharge['receipt_url'] : '';
    $instructionUrl = $voucherUrl !== '' ? $voucherUrl : ($isKonbini ? $receiptUrl : '');
    $instructionUrlLabel = $voucherUrl !== '' ? 'Stripeの支払い案内URL' : ($receiptUrl !== '' ? 'Stripeの領収書URL' : '');

    $copyLines = [];
    foreach ($codes as $code) {
        $copyLines[] = $code['label'] . ': ' . $code['value'];
    }
    if ($instructionUrl !== '') {
        $copyLines[] = $instructionUrlLabel . ': ' . $instructionUrl;
    }
    $displayText = implode("\n", $copyLines);
    if (count($codes) === 0 && $instructionUrl !== '') {
        $copyButtonLabel = 'URLをコピー';
    }
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}

if ($errorMessage === '') {
    $_SESSION['cart'] = [];
}
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>決済受付完了 | SAKURA-NET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f5f4;
            --card: #ffffff;
            --text: #37352f;
            --muted: #615d59;
            --border: rgba(0, 0, 0, 0.1);
            --primary: #0075de;
            --primary-dark: #005fb8;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
            color: var(--text);
            font-family: Inter, "Noto Sans JP", sans-serif;
            line-height: 1.7;
            overflow-x: hidden;
        }

        .page {
            width: min(840px, calc(100% - 32px));
            margin: 0 auto;
            padding: 56px 0 72px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: clamp(24px, 5vw, 40px);
        }

        .eyebrow {
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 12px 0 12px;
            font-size: clamp(28px, 6vw, 44px);
            letter-spacing: -0.04em;
            line-height: 1.15;
        }

        p {
            color: var(--muted);
            overflow-wrap: anywhere;
        }

        .receipt {
            width: 100%;
            max-width: 100%;
            min-height: 140px;
            margin: 18px 0 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            color: var(--text);
            font: 15px/1.7 Inter, "Noto Sans JP", sans-serif;
            resize: vertical;
            background: #fff;
            overflow-wrap: anywhere;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            border: 0;
            border-radius: 4px;
            padding: 11px 16px;
            background: var(--primary);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background 180ms ease, transform 180ms ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .status {
            display: inline-block;
            min-width: 9em;
            margin-left: 12px;
            color: var(--primary);
            font-size: 14px;
            font-weight: 700;
        }

        .details {
            display: grid;
            gap: 10px;
            margin: 20px 0;
        }

        .row {
            display: grid;
            grid-template-columns: minmax(120px, 0.4fr) 1fr;
            gap: 12px;
            border-top: 1px solid var(--border);
            padding-top: 10px;
            color: var(--muted);
            font-size: 14px;
        }

        .row span {
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .row strong {
            color: var(--text);
        }

        .empty {
            margin-top: 18px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            background: #fff;
        }

        @media (max-width: 620px) {
            .row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .status {
                display: block;
                margin: 10px 0 0;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="eyebrow">Payment Complete</div>
            <?php if ($errorMessage !== ''): ?>
                <h1>決済情報を確認できませんでした。</h1>
                <p><?php echo h($errorMessage); ?></p>
                <a class="button" href="order.php">申込ページへ戻る</a>
            <?php else: ?>
                <h1>お支払い受付が完了しました。</h1>
                <p>コンビニ決済を選択された場合は、<br>下記の受付番号またはStripeの案内URLを保存してください。</p>

                <div class="details">
                    <div class="row"><strong>セッションID</strong><span><?php echo h($session['id'] ?? $sessionId); ?></span></div>
                    <div class="row"><strong>決済状態</strong><span><?php echo h((string) ($session['payment_status'] ?? '確認中')); ?></span></div>
                    <?php if (is_numeric($expiresAt)): ?>
                        <div class="row"><strong>支払期限</strong><span><?php echo h(date('Y-m-d H:i', (int) $expiresAt)); ?></span></div>
                    <?php endif; ?>
                </div>

                <?php if ($displayText !== ''): ?>
                    <textarea id="copyTarget" class="receipt" readonly><?php echo h($displayText); ?></textarea>
                    <button id="copyButton" class="button" type="button"><?php echo h($copyButtonLabel); ?></button>
                    <span id="copyStatus" class="status" aria-live="polite"></span>
                <?php else: ?>
                    <div class="empty">
                        <?php if ($isKonbini): ?>
                            <strong>コンビニ受付番号をこの画面で取得できませんでした。</strong>
                            <p>Stripe側で支払い完了済みの場合、受付番号がAPIレスポンスから消えることがあります。Stripeの決済画面、メール、または領収書をご確認ください。</p>
                        <?php else: ?>
                            <strong>コンビニ受付番号はありません。</strong>
                            <p>カード決済の場合、コンビニ受付番号は発行されません。</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($instructionUrl !== ''): ?>
                    <p><a class="button" href="<?php echo h($instructionUrl); ?>" target="_blank" rel="noopener"><?php echo h($instructionUrlLabel); ?>を開く</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

    <script>
        const button = document.getElementById('copyButton');
        const target = document.getElementById('copyTarget');
        const status = document.getElementById('copyStatus');

        function showStatus(message) {
            if (!status) return;
            status.textContent = message;
            window.setTimeout(() => {
                status.textContent = '';
            }, 1500);
        }

        async function copyText() {
            if (!target) return;
            const text = target.value;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                    showStatus('コピーしました');
                    return;
                }
            } catch (error) {
                // Fall through to the mobile-friendly selection fallback.
            }

            target.focus();
            target.select();
            target.setSelectionRange(0, target.value.length);

            try {
                const copied = document.execCommand('copy');
                showStatus(copied ? 'コピーしました' : '選択しました');
            } catch (error) {
                showStatus('選択しました');
            }
        }

        if (button) {
            button.addEventListener('click', copyText);
        }
    </script>
<footer style="border-top:1px solid #e6e8ec;margin-top:32px;padding:20px 16px 40px;color:#6a7180;font-size:13px;text-align:center;font-family:Inter,'Noto Sans JP',sans-serif;">
<a href="tokushoho.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">特定商取引法に基づく表記</a>
<a href="https://sakuranet-co.jp/company.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">会社概要</a>
<a href="https://sakuranet-co.jp/privacy.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">プライバシーポリシー</a>
<a href="https://sakuranet-co.jp/contact.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">お問い合わせ</a>
</footer>
    </body>
</html>
