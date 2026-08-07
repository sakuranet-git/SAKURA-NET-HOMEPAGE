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
    return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
}

function header_field(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function render_page(string $title, string $message, int $status = 200): never
{
    http_response_code($status);
    $safeTitle = h($title);
    $safeMessage = h($message);
    echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>{$safeTitle}｜SAKURA-NET光 お申し込み</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    body{margin:0;background:#fffdfd;color:#2f2a2a;font-family:'Noto Sans JP',sans-serif}.page{width:min(760px,calc(100% - 32px));margin:0 auto;padding:clamp(56px,10vw,120px) 0}.card{border:1px solid #ead8dd;background:#fff;padding:clamp(28px,6vw,58px)}.kicker{font-family:Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#c97b8d;margin:0 0 14px}h1{font-size:clamp(26px,5vw,42px);font-weight:400;line-height:1.5;margin:0 0 18px}p{font-size:15px;line-height:2;color:#6f6262}a{display:inline-flex;margin-top:24px;border:1px solid #a85c70;background:#a85c70;color:#fff;text-decoration:none;padding:13px 24px;font-size:13px;font-weight:700;letter-spacing:.08em}
  </style>
</head>
<body>
  <main class="page">
    <section class="card">
      <p class="kicker">SAKURA-NET Hikari</p>
      <h1>{$safeTitle}</h1>
      <p>{$safeMessage}</p>
      <a href="sakura-net-hikari.html">申込ページへ戻る</a>
    </section>
  </main>
</body>
</html>
HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sakura-net-hikari.html');
    exit;
}

if (field('website') !== '') {
    render_page('送信を受け付けました', '内容を確認のうえ、担当よりご連絡します。');
}

$company = field('company');
$name = field('name');
$email = field('email');
$tel = field('tel');
$installAddress = field('install_address');
$applicationType = field('application_type');
$preferredTiming = field('preferred_timing');
$currentLine = field('current_line');
$message = field('message');
$agreeTerms = field('agree_terms');
$agreePrice = field('agree_price');
$agreePrivacy = field('agree_privacy');

$allowedTypes = ['新規申込', '転用', '事業者変更', '増設・移転', '事前相談'];
$allowedTiming = ['できるだけ早く', '1か月以内', '2〜3か月以内', '時期未定'];

if ($company === '' || $name === '' || $email === '' || $tel === '' || $installAddress === '' || $applicationType === '' || $preferredTiming === '' || $message === '') {
    render_page('入力内容をご確認ください', '必須項目が入力されていません。', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_page('入力内容をご確認ください', 'メールアドレスの形式が正しくありません。', 400);
}

if (!in_array($applicationType, $allowedTypes, true) || !in_array($preferredTiming, $allowedTiming, true)) {
    render_page('入力内容をご確認ください', '申込種別または希望時期の選択内容が正しくありません。', 400);
}

if ($agreeTerms !== '1' || $agreePrice !== '1' || $agreePrivacy !== '1') {
    render_page('同意事項をご確認ください', '約款・別料金表・プライバシーポリシーへの同意が必要です。', 400);
}

if (
    mb_strlen($company) > 120 ||
    mb_strlen($name) > 80 ||
    mb_strlen($email) > 160 ||
    mb_strlen($tel) > 40 ||
    mb_strlen($installAddress) > 220 ||
    mb_strlen($currentLine) > 220 ||
    mb_strlen($message) > 3000
) {
    render_page('入力内容をご確認ください', '入力内容が長すぎます。恐れ入りますが、内容を短くして再送信してください。', 400);
}

$to = 'info@sakuranet-co.jp';
$from = 'noreply@sakuranet-co.jp';
$subject = '【さくらねっとHP】SAKURA-NET光 お申し込み：' . $applicationType;
$submittedAt = date('Y-m-d H:i:s');
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$body = <<<MAIL
SAKURA-NET光 お申し込みフォームから送信がありました。

【送信日時】
{$submittedAt}

【会社名・屋号】
{$company}

【ご担当者名】
{$name}

【メールアドレス】
{$email}

【電話番号】
{$tel}

【設置先住所】
{$installAddress}

【申込種別】
{$applicationType}

【希望時期】
{$preferredTiming}

【現在の回線・利用状況】
{$currentLine}

【ご希望内容・備考】
{$message}

【同意事項】
- SAKURA-NET光 サービス約款に同意済み
- SAKURA-NET光 別料金表を確認済み
- プライバシーポリシーに同意済み

【確認URL】
サービス約款: https://sakuranet-co.jp/img/SAKURA-NET_Hikari_Service_Terms.html
別料金表: https://sakuranet-co.jp/img/SAKURA-NET_Hikari_Service_Terms_Sub.html
プライバシーポリシー: https://sakuranet-co.jp/privacy.html

---
送信元: https://sakuranet-co.jp/apply/sakura-net-hikari.html
IP: {$remoteAddr}
User-Agent: {$userAgent}
MAIL;

$headers = [];
$headers[] = 'From: SAKURA-NET Hikari Form <' . $from . '>';
$headers[] = 'Reply-To: ' . header_field($name) . ' <' . header_field($email) . '>';
$headers[] = 'X-Mailer: PHP/' . phpversion();

$sent = mb_send_mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    render_page('送信に失敗しました', '恐れ入りますが、時間をおいて再度送信するか、info@sakuranet-co.jp までご連絡ください。', 500);
}

render_page('お申し込みを受け付けました', '内容を確認のうえ、通常1〜2営業日以内に担当よりご連絡します。');
