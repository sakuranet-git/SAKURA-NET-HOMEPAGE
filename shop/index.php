<?php
declare(strict_types=1);

$productsPath = __DIR__ . '/products.json';
$products = [];
if (is_file($productsPath)) {
    $decoded = json_decode((string) file_get_contents($productsPath), true);
    if (is_array($decoded)) {
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $amount = filter_var($row['amount'] ?? null, FILTER_VALIDATE_INT);
            if ($id === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $id) || $name === '' || $amount === false || $amount <= 0) {
                continue;
            }
            $products[] = [
                'id' => $id,
                'name' => $name,
                'description' => trim((string) ($row['description'] ?? '')),
                'amount' => (int) $amount,
                'category' => trim((string) ($row['category'] ?? 'Products')),
                'image' => trim((string) ($row['image'] ?? '')),
            ];
        }
    }
}

// カテゴリ別にグループ化（出現順を維持）
$groups = [];
foreach ($products as $p) {
    $groups[$p['category']][] = $p;
}

function sh(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$SITE = 'https://sakuranet-co.jp';
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAKURA-NET SHOP｜UniFi 正規取扱オンラインショップ</title>
    <meta name="description" content="株式会社さくらねっとのUniFi正規取扱オンラインショップ。クラウドゲートウェイ・WiFiアクセスポイント等をクレジットカード・コンビニ決済でご購入いただけます。">
    <link rel="icon" href="<?php echo $SITE; ?>/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #ffffff;
            --tile: #f6f6f8;
            --text: #16181d;
            --muted: #6a7180;
            --border: #e6e8ec;
            --primary: #006fff;
            --primary-dark: #0059cc;
            --radius: 14px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, "Noto Sans JP", sans-serif;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px clamp(16px, 4vw, 40px);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: saturate(180%) blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            font-size: 18px;
            text-decoration: none;
        }

        .brand span { color: var(--primary); }

        .topnav {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .topnav a {
            text-decoration: none;
            color: var(--muted);
            padding: 8px 12px;
            border-radius: 9999px;
        }

        .topnav a:hover { color: var(--text); background: var(--tile); }

        .topnav a.primary { color: #fff; background: var(--text); }

        .wrap { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }

        .hero { padding: clamp(40px, 7vw, 72px) 0 clamp(24px, 4vw, 36px); }

        .eyebrow {
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 12px 0 14px;
            font-size: clamp(30px, 6vw, 54px);
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .hero p {
            max-width: 640px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(15px, 2vw, 17px);
        }

        .cat { padding: clamp(24px, 4vw, 40px) 0; }

        .cat-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .cat-head h2 {
            margin: 0;
            font-size: clamp(20px, 3vw, 28px);
            letter-spacing: -0.02em;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
            gap: 20px;
        }

        .card {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            background: #fff;
            transition: box-shadow 200ms ease, transform 200ms ease, border-color 200ms ease;
        }

        .card:hover {
            border-color: #d3d7de;
            box-shadow: 0 12px 30px rgba(16, 24, 40, 0.08);
            transform: translateY(-3px);
        }

        .thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 4 / 3;
            background: var(--tile);
            padding: 22px;
        }

        .thumb img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .card-body { display: flex; flex-direction: column; gap: 8px; padding: 18px 18px 20px; flex: 1; }

        .tag {
            align-self: flex-start;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--primary);
            background: rgba(0, 111, 255, 0.08);
            padding: 3px 10px;
            border-radius: 9999px;
        }

        .card h3 { margin: 2px 0 0; font-size: 17px; letter-spacing: -0.01em; }

        .card p { margin: 0; color: var(--muted); font-size: 13.5px; flex: 1; }

        .price-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-top: 6px;
        }

        .price { font-size: 22px; font-weight: 800; letter-spacing: -0.03em; }

        .price .tax { font-size: 12px; font-weight: 500; color: var(--muted); }

        .buy {
            margin-top: 12px;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 16px;
            border-radius: 8px;
            transition: background 180ms ease;
        }

        .buy:hover { background: var(--primary-dark); }

        .info {
            margin: clamp(32px, 5vw, 56px) 0;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: clamp(20px, 3vw, 32px);
            background: var(--tile);
        }

        .info h2 { margin: 0 0 16px; font-size: 20px; letter-spacing: -0.02em; }

        .info-row {
            display: grid;
            grid-template-columns: minmax(120px, 0.32fr) 1fr;
            gap: 12px;
            padding: 12px 0;
            border-top: 1px solid var(--border);
            font-size: 14px;
        }

        .info-row:first-of-type { border-top: 0; }

        .info-row strong { color: var(--text); }

        .info-row span { color: var(--muted); }

        .info-row a { color: var(--primary); text-decoration: none; }

        .info-row a:hover { text-decoration: underline; }

        footer {
            border-top: 1px solid var(--border);
            padding: 28px 0 48px;
            color: var(--muted);
            font-size: 13px;
        }

        footer .wrap { display: flex; flex-wrap: wrap; gap: 8px 20px; align-items: center; justify-content: space-between; }

        footer a { color: var(--muted); text-decoration: none; }

        footer a:hover { color: var(--text); }

        @media (max-width: 560px) {
            .info-row { grid-template-columns: 1fr; gap: 4px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php">SAKURA-NET <span>SHOP</span></a>
        <nav class="topnav">
            <a href="<?php echo $SITE; ?>/">本体サイト</a>
            <a href="<?php echo $SITE; ?>/contact.html">お問い合わせ</a>
            <a class="primary" href="#products">商品を見る</a>
        </nav>
    </header>

    <section class="hero">
        <div class="wrap">
            <div class="eyebrow">UniFi 正規取扱</div>
            <h1>UniFi オンラインショップ</h1>
            <p>Ubiquiti UniFi のクラウドゲートウェイ・WiFiアクセスポイントを、株式会社さくらねっとが正規取扱いでお届けします。クレジットカード・コンビニ決済に対応。決済はStripeの安全な画面で行われます。</p>
        </div>
    </section>

    <div id="products">
        <?php foreach ($groups as $catName => $items): ?>
            <section class="cat">
                <div class="wrap">
                    <div class="cat-head">
                        <h2><?php echo sh($catName); ?></h2>
                    </div>
                    <div class="grid">
                        <?php foreach ($items as $p): ?>
                            <article class="card">
                                <div class="thumb">
                                    <?php if ($p['image'] !== ''): ?>
                                        <img src="<?php echo sh($p['image']); ?>" alt="<?php echo sh($p['name']); ?>" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <span class="tag"><?php echo sh($p['category']); ?></span>
                                    <h3><?php echo sh($p['name']); ?></h3>
                                    <p><?php echo sh($p['description']); ?></p>
                                    <div class="price-row">
                                        <span class="price"><?php echo number_format($p['amount']); ?>円 <span class="tax">税込</span></span>
                                    </div>
                                    <a class="buy" href="order.php?product_id=<?php echo urlencode($p['id']); ?>">購入手続きへ進む</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="wrap">
        <section class="info">
            <h2>お買い物・決済について</h2>
            <div class="info-row"><strong>対応決済</strong><span>クレジットカード決済・コンビニ決済（Stripe）。1回のお支払いが30万円を超える場合はカード決済のみとなります。</span></div>
            <div class="info-row"><strong>キャンセル・返金方針</strong><span>お支払い後のキャンセル・返金は、商品発送前に限り個別に確認いたします。発送後の返金は原則として承っておりません。</span></div>
            <div class="info-row"><strong>引渡し時期</strong><span>決済確認後、在庫確認のうえ発送または個別に納期をご案内いたします。コンビニ決済はご入金確認後の手配となります。</span></div>
            <div class="info-row"><strong>お問い合わせ</strong><span>株式会社さくらねっと / 受付時間 10:00-17:00 / <a href="<?php echo $SITE; ?>/contact.html">お問い合わせフォーム</a></span></div>
            <div class="info-row"><strong>事業者情報</strong><span><a href="<?php echo $SITE; ?>/company.html">特定商取引法に基づく表記・会社概要</a></span></div>
            <div class="info-row"><strong>個人情報の取扱い</strong><span><a href="<?php echo $SITE; ?>/privacy.html">プライバシーポリシー</a></span></div>
        </section>
    </div>

    <footer>
        <div class="wrap">
            <span>© <?php echo date('Y'); ?> 株式会社さくらねっと SAKURA-NET SHOP</span>
            <span>
                <a href="<?php echo $SITE; ?>/">本体サイト</a>
                <a href="<?php echo $SITE; ?>/company.html">会社概要</a>
                <a href="<?php echo $SITE; ?>/privacy.html">プライバシー</a>
            </span>
        </div>
    </footer>
</body>
</html>
