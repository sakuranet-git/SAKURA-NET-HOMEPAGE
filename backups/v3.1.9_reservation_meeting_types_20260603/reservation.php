<?php
$today = (new DateTimeImmutable('today', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d');
$error = isset($_GET['error']) ? trim((string)$_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title>来店・オンライン相談予約｜株式会社さくらねっと</title>
  <meta name="description" content="株式会社さくらねっとの来店・オンライン相談予約ページです。希望日時、お名前、連絡先、相談内容を入力して予約をお申し込みいただけます。">
  <link rel="canonical" href="https://sakuranet-co.jp/reservation.php">
  <meta property="og:site_name" content="株式会社さくらねっと">
  <meta property="og:title" content="来店・オンライン相談予約｜株式会社さくらねっと">
  <meta property="og:description" content="希望日時を選んで、来店・オンライン相談の予約希望を送信できます。">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://sakuranet-co.jp/reservation.php">
  <meta property="og:image" content="https://sakuranet-co.jp/img/ogp.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .p-reservation-lead { display:grid; grid-template-columns:minmax(0,1fr) minmax(280px,420px); gap:clamp(24px,5vw,64px); align-items:start; }
    .p-reservation-copy h2 { font-family:var(--font-jp); font-size:clamp(28px,4.2vw,48px); font-weight:300; line-height:1.45; letter-spacing:0; color:var(--color-text); margin-bottom:20px; }
    .p-reservation-copy p { font-size:15px; line-height:2; color:var(--color-text-sub); }
    .p-reservation-steps { background:var(--color-bg-alt); border:1px solid var(--color-border); border-radius:12px; padding:clamp(20px,4vw,32px); }
    .p-reservation-steps_title, .p-reservation-form_label { font-family:var(--font-en); font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--color-accent); margin-bottom:14px; }
    .p-reservation-steps ol { display:grid; gap:14px; counter-reset:step; list-style:none; margin:0; padding:0; }
    .p-reservation-steps li { position:relative; padding-left:42px; font-size:14px; line-height:1.8; color:var(--color-text); }
    .p-reservation-steps li::before { counter-increment:step; content:counter(step); position:absolute; left:0; top:2px; width:28px; height:28px; border-radius:9999px; display:grid; place-items:center; background:var(--color-accent-pale); color:var(--color-accent-d); font-family:var(--font-en); font-size:12px; font-weight:700; }
    .p-reservation-form { background:#fffdfd; border:1px solid var(--color-border); border-radius:12px; box-shadow:var(--shadow-card); padding:clamp(22px,4vw,42px); }
    .p-reservation-form_head { display:flex; justify-content:space-between; gap:20px; align-items:start; margin-bottom:clamp(24px,4vw,36px); padding-bottom:20px; border-bottom:1px solid var(--color-border); }
    .p-reservation-form_title { font-size:clamp(20px,2.5vw,28px); font-weight:400; line-height:1.5; color:var(--color-text); letter-spacing:0; }
    .p-reservation-form_note { max-width:360px; font-size:13px; line-height:1.9; color:var(--color-text-sub); }
    .p-reservation-error { border:1px solid rgba(168,92,112,0.35); border-left:4px solid var(--color-accent-d); background:var(--color-accent-pale); color:var(--color-text); border-radius:8px; padding:14px 16px; margin-bottom:22px; font-size:14px; line-height:1.8; }
    .p-reservation-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px; }
    .p-reservation-field { display:flex; flex-direction:column; gap:8px; }
    .p-reservation-field--full { grid-column:1 / -1; }
    .p-reservation-field label, .p-reservation-privacy { font-size:13px; font-weight:500; color:var(--color-text); line-height:1.7; }
    .p-reservation-required { display:inline-block; margin-left:8px; padding:2px 8px; border-radius:9999px; background:var(--color-accent-pale); color:var(--color-accent-d); font-size:11px; font-weight:700; line-height:1.4; }
    .p-reservation-field input, .p-reservation-field select, .p-reservation-field textarea { width:100%; border:1px solid var(--color-border-med); border-radius:8px; background:#fff; color:var(--color-text); font:inherit; font-size:15px; letter-spacing:0; padding:13px 14px; min-height:48px; transition:border-color 0.25s var(--ease-out), box-shadow 0.25s var(--ease-out); }
    .p-reservation-field textarea { min-height:140px; resize:vertical; line-height:1.8; }
    .p-reservation-field input:focus, .p-reservation-field select:focus, .p-reservation-field textarea:focus { outline:none; border-color:var(--color-accent); box-shadow:0 0 0 4px rgba(201,123,141,0.14); }
    .p-reservation-help { font-size:12px; line-height:1.7; color:var(--color-text-sub); }
    .p-reservation-privacy { display:flex; gap:12px; align-items:flex-start; background:var(--color-bg-alt); border:1px solid var(--color-border); border-radius:8px; padding:16px; }
    .p-reservation-privacy input { width:18px; height:18px; margin-top:3px; flex:0 0 auto; }
    .p-reservation-privacy a { text-decoration:underline; text-underline-offset:3px; }
    .p-reservation-honeypot { position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden; }
    .p-reservation-actions { margin-top:30px; display:flex; flex-wrap:wrap; align-items:center; gap:16px; }
    .p-reservation-button { min-width:min(100%,260px); border-radius:9999px; background:var(--color-text); color:#fff; font-size:15px; font-weight:700; padding:15px 30px; transition:background 0.25s var(--ease-out), transform 0.25s var(--ease-out); }
    .p-reservation-button:hover { background:var(--color-accent-d); transform:translateY(-1px); }
    .p-reservation-actions p { font-size:12px; line-height:1.7; color:var(--color-text-sub); margin:0; }
    @media (max-width:840px) { .p-reservation-lead, .p-reservation-grid { grid-template-columns:1fr; } .p-reservation-form_head { flex-direction:column; } }
    @media (max-width:480px) { .l-header { padding-inline:12px; } .l-header_logo img { height:40px !important; } .l-header_contact a { padding:9px 14px !important; } }
  </style>
</head>
<body class="is-loading">
  <div class="l-cover" aria-hidden="true"><div class="l-cover_logo"><img src="img/logo_loading.png" alt=""></div></div>

  <header class="l-header" role="banner">
    <div class="l-header_logo"><a href="index.html" aria-label="株式会社さくらねっと トップへ"><img src="img/logo_header_light_bg.png" alt="SAKURA-NET" style="height:48px;width:auto;display:block;"></a></div>
    <nav class="l-header_list" aria-label="グローバルナビゲーション"><ul style="display:contents;list-style:none;">
      <li><a href="index.html"><span class="c-word c-word-hover js-word" data-order="0">Home</span></a></li>
      <li><a href="service.html"><span class="c-word c-word-hover js-word" data-order="1">Services</span></a></li>
      <li><a href="company.html"><span class="c-word c-word-hover js-word" data-order="2">About</span></a></li>
      <li><a href="news.html"><span class="c-word c-word-hover js-word" data-order="3">News</span></a></li>
      <li><a href="contact.html"><span class="c-word c-word-hover js-word" data-order="4">Contact</span></a></li>
    </ul></nav>
    <div class="l-header_contact"><a href="reservation.php" style="font-family:'Outfit',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#fff;background:#1a1818;padding:10px 20px;border-radius:9999px;display:inline-block;">予約する</a></div>
    <button class="l-header_toggle js-nav-toggle" aria-expanded="false" aria-controls="l-nav" aria-label="メニューを開く"><span></span><span></span><span></span></button>
  </header>

  <nav class="l-nav" id="l-nav" aria-label="モバイルナビゲーション">
    <div class="l-nav_inner"><ul class="l-nav_list">
      <li><a href="index.html" data-title="HOME" data-caption="トップページ"><span class="c-word js-word" data-order="0">HOME</span></a></li>
      <li><a href="service.html" data-title="SERVICES" data-caption="サービス"><span class="c-word js-word" data-order="1">SERVICES</span></a></li>
      <li><a href="access.html" data-title="ACCESS" data-caption="アクセス"><span class="c-word js-word" data-order="2">ACCESS</span></a></li>
      <li><a href="reservation.php" data-title="RESERVATION" data-caption="来店・オンライン相談予約"><span class="c-word js-word" data-order="3">RESERVATION</span></a></li>
      <li><a href="contact.html" data-title="CONTACT" data-caption="お問い合わせ"><span class="c-word js-word" data-order="4">CONTACT</span></a></li>
    </ul></div>
  </nav>

  <main>
    <div class="c-page-header">
      <div class="l-inner">
        <nav class="c-breadcrumb" aria-label="パンくずリスト"><a href="index.html">Home</a><span class="c-breadcrumb_sep" aria-hidden="true"></span><span aria-current="page">Reservation</span></nav>
        <p class="c-page-header_label">Reservation</p>
        <h1 class="c-page-header_title">Reservation</h1>
        <p class="c-page-header_sub c-page-header_title--jp">来店・オンライン相談予約</p>
      </div>
    </div>

    <section class="c-section" aria-labelledby="reservation-title">
      <div class="l-inner">
        <div class="p-reservation-lead js-anim">
          <div class="p-reservation-copy">
            <h2 id="reservation-title">希望日時を選んで、相談予約を送信できます。</h2>
            <p>さくらねっとへの来店相談・オンライン相談の予約受付です。一般的なお問い合わせは <a href="contact.html" style="text-decoration:underline;text-underline-offset:3px;">お問い合わせページ</a> から、日時を決めて相談したい場合はこちらからお申し込みください。</p>
          </div>
          <div class="p-reservation-steps" aria-label="予約の流れ">
            <p class="p-reservation-steps_title">Flow</p>
            <ol>
              <li>下のフォームで希望日時と連絡先を入力します。</li>
              <li>担当者が内容と空き状況を確認します。</li>
              <li>予約確定のご連絡をもって予約完了です。</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="c-section c-section--alt" aria-labelledby="reservation-form-title">
      <div class="l-inner">
        <form class="p-reservation-form js-anim" action="reservation_send.php" method="post" autocomplete="on">
          <div class="p-reservation-form_head">
            <div><p class="p-reservation-form_label">Reservation Form</p><h2 class="p-reservation-form_title" id="reservation-form-title">予約内容を入力してください</h2></div>
            <p class="p-reservation-form_note">日程調整のため、希望日・時間帯・連絡先は必ず入力してください。</p>
          </div>
          <?php if ($error !== ''): ?><div class="p-reservation-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

          <div class="p-reservation-grid">
            <div class="p-reservation-field"><label for="preferred_date_1">第一希望日<span class="p-reservation-required">必須</span></label><input type="date" id="preferred_date_1" name="preferred_date_1" min="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></div>
            <div class="p-reservation-field"><label for="preferred_date_2">第二希望日</label><input type="date" id="preferred_date_2" name="preferred_date_2" min="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
            <div class="p-reservation-field"><label for="preferred_date_3">第三希望日</label><input type="date" id="preferred_date_3" name="preferred_date_3" min="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
            <div class="p-reservation-field"><label for="time_slot">希望時間<span class="p-reservation-required">必須</span></label><select id="time_slot" name="time_slot" required><option value="">選択してください</option><option value="10:00|11:00">10:00 - 11:00</option><option value="11:00|12:00">11:00 - 12:00</option><option value="13:00|14:00">13:00 - 14:00</option><option value="14:00|15:00">14:00 - 15:00</option><option value="15:00|16:00">15:00 - 16:00</option><option value="16:00|17:00">16:00 - 17:00</option><option value="17:00|18:00">17:00 - 18:00</option></select></div>
            <div class="p-reservation-field"><label for="meeting_type">相談方法<span class="p-reservation-required">必須</span></label><select id="meeting_type" name="meeting_type" required><option value="">選択してください</option><option value="来店相談">来店相談</option><option value="オンライン相談">オンライン相談</option><option value="電話相談">電話相談</option></select></div>
            <div class="p-reservation-field"><label for="participant_count">参加人数<span class="p-reservation-required">必須</span></label><select id="participant_count" name="participant_count" required><option value="">選択してください</option><option value="1">1名</option><option value="2">2名</option><option value="3">3名</option><option value="4">4名</option><option value="5">5名</option><option value="6">6名以上</option></select><p class="p-reservation-help">来店・オンライン相談に参加される人数を選択してください。</p></div>
            <div class="p-reservation-field"><label for="service_type">相談したい内容<span class="p-reservation-required">必須</span></label><select id="service_type" name="service_type" required><option value="">選択してください</option><option value="初回相談">初回相談</option><option value="SAKURA-NET光・回線相談">SAKURA-NET光・回線相談</option><option value="UniFi・ネットワーク相談">UniFi・ネットワーク相談</option><option value="防犯カメラ・入退室管理">防犯カメラ・入退室管理</option><option value="法人ITサポート">法人ITサポート</option><option value="リモートサポート">リモートサポート</option><option value="その他">その他</option></select></div>
            <div class="p-reservation-field"><label for="company">会社名・屋号</label><input type="text" id="company" name="company" maxlength="120" autocomplete="organization" placeholder="個人の方は空欄で構いません"></div>
            <div class="p-reservation-field"><label for="customer_name">お名前<span class="p-reservation-required">必須</span></label><input type="text" id="customer_name" name="customer_name" maxlength="80" autocomplete="name" required></div>
            <div class="p-reservation-field"><label for="customer_email">メールアドレス<span class="p-reservation-required">必須</span></label><input type="email" id="customer_email" name="customer_email" maxlength="160" autocomplete="email" required></div>
            <div class="p-reservation-field"><label for="customer_phone">電話番号<span class="p-reservation-required">必須</span></label><input type="tel" id="customer_phone" name="customer_phone" maxlength="40" autocomplete="tel" required></div>
            <div class="p-reservation-field p-reservation-field--full"><label for="message">相談内容・補足</label><textarea id="message" name="message" maxlength="3000" placeholder="例：会社のWi-Fiを安定させたい、回線を相談したい、来店前に確認したい内容がある など"></textarea><p class="p-reservation-help">短くても大丈夫です。担当者が確認して折り返します。</p></div>
            <div class="p-reservation-field p-reservation-field--full p-reservation-honeypot" aria-hidden="true"><label for="website">Webサイト</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
            <div class="p-reservation-field p-reservation-field--full"><label class="p-reservation-privacy"><input type="checkbox" name="privacy" value="1" required><span><a href="privacy.html" target="_blank" rel="noopener">プライバシーポリシー</a>を確認し、入力内容を予約受付のために送信することに同意します。<span class="p-reservation-required">必須</span></span></label></div>
          </div>

          <div class="p-reservation-actions"><button type="submit" class="p-reservation-button">予約希望を送信する</button><p>送信だけでは予約確定ではありません。担当者からの連絡後に確定します。</p></div>
        </form>
      </div>
    </section>

    <section class="c-section c-section--dark" aria-labelledby="reservation-contact-title">
      <div class="l-inner u-text-center">
        <p class="c-section_label" style="color:#c97b8d;">Contact</p>
        <h2 style="font-family:'Noto Sans JP',sans-serif;font-size:clamp(22px,3.5vw,40px);font-weight:300;color:#fff;margin-bottom:24px;letter-spacing:0;" id="reservation-contact-title">お急ぎの場合はお電話ください。</h2>
        <div style="font-family:'Outfit',sans-serif;font-size:clamp(24px,4vw,40px);font-weight:700;color:#fff;margin-bottom:8px;"><a href="tel:0677772720" style="color:inherit;">06-7777-2720</a></div>
        <p style="font-size:13px;color:rgba(255,255,255,0.56);">平日 10:00-19:00 ／ 完全予約制</p>
      </div>
    </section>
  </main>

  <footer class="l-footer" role="contentinfo">
    <div class="l-footer_inner">
      <div class="l-footer_top">
        <div><div class="l-footer_logo"><img src="img/logo_header_dark_bg.png" alt="SAKURA-NET" style="height:40px;width:auto;"></div><div class="l-footer_info" style="margin-top:16px;">株式会社さくらねっと<br>〒532-0012 大阪市淀川区木川東4-3-34 5F<br>TEL: <a href="tel:0677772720">06-7777-2720</a> MAIL: <a href="mailto:info@sakuranet-co.jp">info@sakuranet-co.jp</a><br>営業時間: 平日 10:00-19:00（完全予約制）</div></div>
        <nav class="l-footer_nav" aria-label="フッターナビゲーション"><a href="index.html">Home</a><a href="service.html">Services</a><a href="company.html">About</a><a href="access.html">Access</a><a href="reservation.php">Reservation</a><a href="contact.html">Contact</a><a href="https://sakuranet-co.jp/system/index.php">SYSTEM</a><a href="privacy.html">Privacy</a></nav>
      </div>
      <div class="l-footer_bottom"><small class="l-footer_copy">&copy; 2026 SAKURA-NET Inc. All Rights Reserved.</small><div class="l-footer_links"><a href="privacy.html">プライバシーポリシー</a><a href="contact.html">お問い合わせ</a></div></div>
    </div>
  </footer>

  <div class="l-mobile-cta" aria-label="スマートフォン用クイックアクセス">
    <a href="tel:0677772720" class="l-mobile-cta_btn l-mobile-cta_btn--tel"><span class="l-mobile-cta_icon" aria-hidden="true">TEL</span><span>電話で相談</span></a>
    <a href="#reservation-form-title" class="l-mobile-cta_btn l-mobile-cta_btn--form"><span class="l-mobile-cta_icon" aria-hidden="true">予約</span><span>予約フォーム</span></a>
  </div>

  <script src="js/main.js"></script>
</body>
</html>
