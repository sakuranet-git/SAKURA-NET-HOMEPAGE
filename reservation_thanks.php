<?php
$ticket = isset($_GET['ticket']) ? trim((string)$_GET['ticket']) : '';
$safeTicket = preg_match('/^(RSV|CS)-[0-9]{8}-[0-9]{4}$/', $ticket) ? $ticket : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title>予約希望を受け付けました｜株式会社さくらねっと</title>
  <meta name="description" content="株式会社さくらねっとの予約希望受付完了ページです。">
  <meta name="robots" content="noindex,follow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="is-loading">
  <div class="l-cover" aria-hidden="true"><div class="l-cover_logo"><img src="img/logo_loading.png" alt=""></div></div>
  <header class="l-header" role="banner">
    <div class="l-header_logo"><a href="index.html" aria-label="株式会社さくらねっと トップへ"><img src="img/logo_header_light_bg.png" alt="SAKURA-NET" style="height:48px;width:auto;display:block;"></a></div>
    <nav class="l-header_list" aria-label="グローバルナビゲーション"><ul>
      <li><a href="index.html"><span class="c-word c-word-hover js-word" data-order="0">Home</span></a></li>
      <li><a href="service.html"><span class="c-word c-word-hover js-word" data-order="1">Services</span></a></li>
      <li><a href="contact.html"><span class="c-word c-word-hover js-word" data-order="2">Contact</span></a></li>
    </ul></nav>
    <div class="l-header_contact"><a href="reservation.php" style="font-family:'Outfit',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#fff;background:#1a1818;padding:10px 20px;border-radius:9999px;display:inline-block;">予約する</a></div>
  </header>

  <main>
    <div class="c-page-header">
      <div class="l-inner">
        <nav class="c-breadcrumb" aria-label="パンくずリスト"><a href="index.html">Home</a><span class="c-breadcrumb_sep" aria-hidden="true"></span><span aria-current="page">Reservation Complete</span></nav>
        <p class="c-page-header_label">Reservation</p>
        <h1 class="c-page-header_title">Accepted</h1>
        <p class="c-page-header_sub c-page-header_title--jp">予約希望を受け付けました</p>
      </div>
    </div>

    <section class="c-section" aria-labelledby="thanks-title">
      <div class="l-inner l-inner--narrow">
        <div class="js-anim" style="background:#fffdfd;border:1px solid var(--color-border);border-radius:12px;box-shadow:var(--shadow-card);padding:clamp(24px,5vw,48px);">
          <p class="c-section_label">Thank you</p>
          <h2 id="thanks-title" style="font-family:'Noto Sans JP',sans-serif;font-size:clamp(24px,4vw,40px);font-weight:300;line-height:1.5;letter-spacing:0;color:var(--color-text);margin-bottom:18px;">送信ありがとうございます。</h2>
          <p style="font-size:15px;line-height:2;color:var(--color-text-sub);margin-bottom:22px;">担当者が内容を確認し、予約確定のご連絡をいたします。送信時点では予約確定ではありませんので、当社からの連絡をお待ちください。</p>
          <?php if ($safeTicket !== ''): ?>
            <p style="background:var(--color-bg-alt);border:1px solid var(--color-border);border-radius:8px;padding:16px;font-family:'Outfit','Noto Sans JP',sans-serif;font-size:16px;font-weight:700;color:var(--color-text);margin-bottom:22px;">受付番号: <?php echo htmlspecialchars($safeTicket, ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>
          <div style="display:flex;flex-wrap:wrap;gap:12px;"><a href="index.html" class="c-button">トップページへ戻る</a><a href="reservation.php" class="c-button">別の日時で予約する</a></div>
        </div>
      </div>
    </section>
  </main>

  <script src="js/main.js"></script>
</body>
</html>
