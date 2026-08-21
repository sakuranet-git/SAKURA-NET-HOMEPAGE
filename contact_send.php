<?php
declare(strict_types=1);

mb_language('Japanese');
mb_internal_encoding('UTF-8');

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field(string $key): string
{
    $value = $_POST[$key] ?? '';
    if (is_array($value)) {
        return '';
    }
    return trim(str_replace(["\r\n", "\r"], "\n", (string)$value));
}

function header_field(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function config_value(string $name, string $default = ''): string
{
    return defined($name) ? (string) constant($name) : $default;
}

function verify_form_token(string $token, string $signature): bool
{
    $secret = config_value('CONTACT_FORM_HMAC_SECRET');
    if ($secret === '' || $token === '' || $signature === '') {
        return false;
    }

    $expected = hash_hmac('sha256', $token, $secret);
    if (!hash_equals($expected, $signature)) {
        return false;
    }

    $parts = explode(':', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return false;
    }

    $issuedAt = (int) $parts[0];
    $age = time() - $issuedAt;

    return $age >= 3 && $age <= 7200;
}

function rate_limit_key(string $ip): string
{
    return hash('sha256', $ip);
}

function check_rate_limit(string $ip): bool
{
    $dir = config_value('CONTACT_RATE_LIMIT_DIR', sys_get_temp_dir() . '/sakura_contact_security');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $file = rtrim($dir, '/\\') . '/' . rate_limit_key($ip) . '.json';
    $now = time();
    $windowSeconds = 600;
    $maxAttempts = 6;
    $attempts = [];

    $handle = fopen($file, 'c+');
    if ($handle === false) {
        return true;
    }

    flock($handle, LOCK_EX);
    $raw = stream_get_contents($handle);
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $attempts = array_values(array_filter($decoded, static fn ($ts): bool => is_int($ts) && $ts > $now - $windowSeconds));
        }
    }

    $attempts[] = $now;
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($attempts));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return count($attempts) <= $maxAttempts;
}

function verify_turnstile(string $token, string $ip): bool
{
    $secret = config_value('CONTACT_TURNSTILE_SECRET_KEY');
    if ($secret === '') {
        return true;
    }

    if ($token === '' || strlen($token) > 2048) {
        return false;
    }

    $payload = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $ip,
    ], '', '&');

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 8,
        ],
    ]);

    $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
    if (!is_string($response) || $response === '') {
        return false;
    }

    $result = json_decode($response, true);

    return is_array($result) && ($result['success'] ?? false) === true;
}

function render_error(string $message): never
{
    http_response_code(400);
    $safeMessage = h($message);
    echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>送信内容をご確認ください｜株式会社さくらねっと</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="c-section" style="min-height:70vh;display:flex;align-items:center;">
    <div class="l-inner l-inner--narrow">
      <p class="c-section_label">Contact Error</p>
      <h1 class="c-section_title c-section_title--jp">送信内容をご確認ください。</h1>
      <p class="c-section_sub">{$safeMessage}</p>
      <p style="margin-top:32px;"><a href="contact.html#contact-form" class="c-button">フォームへ戻る</a></p>
    </div>
  </main>
</body>
</html>
HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$configPath = __DIR__ . '/contact_security_config.php';
if (is_file($configPath)) {
    require $configPath;
}

if (field('website') !== '') {
    header('Location: contact_thanks.html');
    exit;
}

$ip = client_ip();
$formToken = field('form_token');
$formTokenSig = field('form_token_sig');
$turnstileToken = field('cf-turnstile-response');

if (!check_rate_limit($ip)) {
    render_error('短時間に送信が集中しています。恐れ入りますが、時間を置いて再度お試しください。');
}

if (!verify_form_token($formToken, $formTokenSig)) {
    render_error('Bot対策チェックに失敗しました。ページを再読み込みしてから、数秒待って再度送信してください。');
}

if (!verify_turnstile($turnstileToken, $ip)) {
    render_error('Bot対策チェックに失敗しました。チェック完了後に再度送信してください。');
}

$company = field('company');
$name = field('name');
$email = field('email');
$tel = field('tel');
$category = field('category');
$message = field('message');
$privacy = field('privacy');

$allowedCategories = [
    'UniFi導入・ネットワーク構築',
    'SAKURA-NET光・回線相談',
    '防犯カメラ・入退室管理',
    'クラウドPBX・法人電話',
    '法人ITサポート・保守',
    '代理店・OEM相談',
    'その他',
];

if ($company === '' || $name === '' || $email === '' || $tel === '' || $category === '' || $message === '') {
    render_error('必須項目が入力されていません。');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_error('メールアドレスの形式が正しくありません。');
}

if (!in_array($category, $allowedCategories, true)) {
    render_error('ご相談内容カテゴリを選択してください。');
}

if ($privacy !== '1') {
    render_error('プライバシーポリシーへの同意が必要です。');
}

if (mb_strlen($company) > 120 || mb_strlen($name) > 80 || mb_strlen($email) > 160 || mb_strlen($tel) > 40 || mb_strlen($message) > 3000) {
    render_error('入力内容が長すぎます。恐れ入りますが、内容を短くして再送信してください。');
}

$to = 'info@sakuranet-co.jp';
$from = 'noreply@sakuranet-co.jp';
$subject = '【さくらねっとHP】お問い合わせ：' . $category;

$body = <<<MAIL
さくらねっとHPのお問い合わせフォームから送信がありました。

【会社名】
{$company}

【お名前】
{$name}

【メールアドレス】
{$email}

【電話番号】
{$tel}

【ご相談内容カテゴリ】
{$category}

【お問い合わせ内容】
{$message}

---
送信元: https://sakuranet-co.jp/contact.html
MAIL;

$headers = [];
$headers[] = 'From: SAKURA-NET Web Form <' . $from . '>';
$headers[] = 'Reply-To: ' . header_field($name) . ' <' . header_field($email) . '>';
$headers[] = 'X-Mailer: PHP/' . phpversion();

// mb_send_mail が mb_language('Japanese') に基づき ISO-2022-JP へ自動変換するため
// Content-Type は mb_send_mail に任せる（手動指定すると文字化けする）
$sent = mb_send_mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    render_error('送信に失敗しました。恐れ入りますが、お電話またはメールでお問い合わせください。');
}

header('Location: contact_thanks.html');
exit;
