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

if (field('website') !== '') {
    header('Location: contact_thanks.html');
    exit;
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
