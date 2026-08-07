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
  <title>{$safeTitle}｜SAKURA-NET Mobile / SIM お申し込み</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    body{margin:0;background:#fffdfd;color:#2f2a2a;font-family:'Noto Sans JP',sans-serif}.page{width:min(760px,calc(100% - 32px));margin:0 auto;padding:clamp(56px,10vw,120px) 0}.card{border:1px solid #ead8dd;background:#fff;padding:clamp(28px,6vw,58px)}.kicker{font-family:Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#c97b8d;margin:0 0 14px}h1{font-size:clamp(26px,5vw,42px);font-weight:400;line-height:1.5;margin:0 0 18px}p{font-size:15px;line-height:2;color:#6f6262}a{display:inline-flex;margin-top:24px;border:1px solid #a85c70;background:#a85c70;color:#fff;text-decoration:none;padding:13px 24px;font-size:13px;font-weight:700;letter-spacing:.08em}
  </style>
</head>
<body>
  <main class="page">
    <section class="card">
      <p class="kicker">SAKURA-NET Mobile / SIM</p>
      <h1>{$safeTitle}</h1>
      <p>{$safeMessage}</p>
      <a href="sakura-net-mobile.html">申込ページへ戻る</a>
    </section>
  </main>
</body>
</html>
HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sakura-net-mobile.html');
    exit;
}

if (field('website') !== '') {
    render_page('送信を受け付けました', '内容を確認のうえ、担当よりご連絡します。');
}

$company = field('company');
$name = field('name');
$email = field('email');
$tel = field('tel');
$billingAddress = field('billing_address');
$applicationType = field('application_type');
$simCount = field('sim_count');
$useCase = field('use_case');
$preferredTiming = field('preferred_timing');
$deviceRequest = field('device_request');
$message = field('message');
$agreeMineoTerms = field('agree_mineo_terms');
$agreeServiceNotice = field('agree_service_notice');
$agreePrivacy = field('agree_privacy');

$allowedTypes = ['新規申込', '台数追加', '貸し出し', '端末・SIM変更', 'VPN-SIM相談', 'M2M・IoT相談', '事前相談'];
$allowedCounts = ['1回線', '2〜5回線', '6〜10回線', '11〜30回線', '31回線以上', '未定'];
$allowedUseCases = ['社用スマートフォン', 'モバイルルーター', 'M2M・IoT機器', 'バックアップ回線', '閉域・VPN用途', 'その他'];
$allowedTiming = ['できるだけ早く', '1か月以内', '2〜3か月以内', '時期未定'];

if ($company === '' || $name === '' || $email === '' || $tel === '' || $billingAddress === '' || $applicationType === '' || $simCount === '' || $useCase === '' || $preferredTiming === '' || $message === '') {
    render_page('入力内容をご確認ください', '必須項目が入力されていません。', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_page('入力内容をご確認ください', 'メールアドレスの形式が正しくありません。', 400);
}

if (
    !in_array($applicationType, $allowedTypes, true) ||
    !in_array($simCount, $allowedCounts, true) ||
    !in_array($useCase, $allowedUseCases, true) ||
    !in_array($preferredTiming, $allowedTiming, true)
) {
    render_page('入力内容をご確認ください', '選択内容が正しくありません。', 400);
}

if ($agreeMineoTerms !== '1' || $agreeServiceNotice !== '1' || $agreePrivacy !== '1') {
    render_page('同意事項をご確認ください', '約款・提供条件・プライバシーポリシーへの同意が必要です。', 400);
}

if (
    mb_strlen($company) > 120 ||
    mb_strlen($name) > 80 ||
    mb_strlen($email) > 160 ||
    mb_strlen($tel) > 40 ||
    mb_strlen($billingAddress) > 220 ||
    mb_strlen($deviceRequest) > 220 ||
    mb_strlen($message) > 3000
) {
    render_page('入力内容をご確認ください', '入力内容が長すぎます。恐れ入りますが、内容を短くして再送信してください。', 400);
}

$to = 'info@sakuranet-co.jp';
$from = 'noreply@sakuranet-co.jp';
$subject = '【さくらねっとHP】SAKURA-NET Mobile / SIM お申し込み：' . $applicationType;
$submittedAt = date('Y-m-d H:i:s');
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$body = <<<MAIL
SAKURA-NET Mobile / SIM お申し込みフォームから送信がありました。

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

【契約者住所・請求先住所】
{$billingAddress}

【申込種別】
{$applicationType}

【予定回線数】
{$simCount}

【主な用途】
{$useCase}

【希望時期】
{$preferredTiming}

【端末・オプション希望】
{$deviceRequest}

【ご希望内容・備考】
{$message}

【同意事項】
- mineo法人 約款・規約一覧を確認済み
- SAKURA-NET Mobile / SIMがmineo BiZ法人向けサービスをベースにしたOEMサービスであることを確認済み
- プライバシーポリシーに同意済み

【確認URL】
mineo法人 約款・規約一覧: https://support.mineo.jp/business/agreelist.html
SAKURA-NET Mobile / SIM: https://sakuranet-co.jp/sakura-net-mobile.html
プライバシーポリシー: https://sakuranet-co.jp/privacy.html

---
送信元: https://sakuranet-co.jp/apply/sakura-net-mobile.html
IP: {$remoteAddr}
User-Agent: {$userAgent}
MAIL;

$headers = [];
$headers[] = 'From: SAKURA-NET Mobile Form <' . $from . '>';
$headers[] = 'Reply-To: ' . header_field($name) . ' <' . header_field($email) . '>';
$headers[] = 'X-Mailer: PHP/' . phpversion();

$sent = mb_send_mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    render_page('送信に失敗しました', '恐れ入りますが、時間をおいて再度送信するか、info@sakuranet-co.jp までご連絡ください。', 500);
}

render_page('お申し込みを受け付けました', '内容を確認のうえ、通常1〜2営業日以内に担当よりご連絡します。');
