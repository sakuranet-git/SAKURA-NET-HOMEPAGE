<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

function sh(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cart_count(): int
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) {
        return 0;
    }

    $count = 0;
    foreach ($cart as $quantity) {
        $count += max(0, (int) $quantity);
    }

    return $count;
}

$products = array_values(checkout_products());
$groups = [];
foreach ($products as $product) {
    $groups[(string) $product['category']][] = $product;
}

$site = 'https://sakuranet-co.jp';
$added = (string) ($_GET['cart_added'] ?? '') === '1';
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAKURA-NET オンラインショップ | 株式会社さくらねっと</title>
    <meta name="description" content="株式会社さくらねっとのオンラインショップです。サポート・保守サービスとUniFi正規取扱いネットワーク機器をまとめて購入できます。">
    <link rel="icon" href="<?php echo sh($site); ?>/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#fff;--tile:#f6f6f8;--text:#16181d;--muted:#6a7180;--border:#e6e8ec;--primary:#006fff;--primary-dark:#0059cc;--radius:14px}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7;-webkit-font-smoothing:antialiased}
        a{color:inherit}
        .topbar{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px clamp(16px,4vw,40px);background:rgba(255,255,255,.9);backdrop-filter:saturate(180%) blur(12px);border-bottom:1px solid var(--border)}
        .brand{font-weight:800;letter-spacing:-.02em;font-size:18px;text-decoration:none}.brand span{color:var(--primary)}
        .topnav{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600}.topnav a{text-decoration:none;color:var(--muted);padding:8px 12px;border-radius:9999px;white-space:nowrap}.topnav a:hover{color:var(--text);background:var(--tile)}.topnav a.primary{color:#fff;background:var(--text)}
        .wrap{width:min(1120px,calc(100% - 32px));margin:0 auto}
        .hero{padding:clamp(40px,7vw,72px) 0 clamp(24px,4vw,36px)}.eyebrow{color:var(--primary);font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}.hero h1{margin:12px 0 14px;font-size:clamp(30px,6vw,54px);letter-spacing:-.04em;line-height:1.1}.hero p{max-width:720px;margin:0;color:var(--muted);font-size:clamp(15px,2vw,17px)}
        .notice{margin-top:20px;display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(0,111,255,.2);background:rgba(0,111,255,.07);color:var(--primary);border-radius:9999px;padding:9px 14px;font-weight:700;font-size:13px}
        .catnav{position:sticky;top:56px;z-index:15;background:rgba(255,255,255,.92);backdrop-filter:saturate(180%) blur(12px);border-bottom:1px solid var(--border)}.catnav .wrap{display:flex;gap:8px;overflow-x:auto;padding:11px 0}.catnav a{white-space:nowrap;text-decoration:none;color:var(--muted);font-size:13px;font-weight:600;padding:7px 14px;border-radius:9999px;border:1px solid var(--border)}.catnav a:hover{color:var(--text);background:var(--tile)}
        .cat{padding:clamp(24px,4vw,40px) 0;scroll-margin-top:116px}.cat-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:18px}.cat-head h2{margin:0;font-size:clamp(20px,3vw,28px);letter-spacing:-.02em}.cat-count{color:var(--muted);font-size:13px;font-weight:600}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));gap:20px}.card{display:flex;flex-direction:column;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;background:#fff;transition:box-shadow .2s ease,transform .2s ease,border-color .2s ease}.card:hover{border-color:#d3d7de;box-shadow:0 12px 30px rgba(16,24,40,.08);transform:translateY(-3px)}
        .thumb{display:flex;align-items:center;justify-content:center;aspect-ratio:4/3;background:var(--tile);padding:22px}.thumb img{max-width:100%;max-height:100%;object-fit:contain}.thumb-ph{display:flex;align-items:center;justify-content:center;width:100%;height:100%;color:#b9c0cc;font-weight:800;letter-spacing:-.02em;font-size:22px}.thumb-ph b{color:var(--primary)}
        .card-body{display:flex;flex-direction:column;gap:8px;padding:18px 18px 20px;flex:1}.tag{align-self:flex-start;font-size:11px;font-weight:700;letter-spacing:.06em;color:var(--primary);background:rgba(0,111,255,.08);padding:3px 10px;border-radius:9999px}.card h3{margin:2px 0 0;font-size:17px;letter-spacing:-.01em}.card p{margin:0;color:var(--muted);font-size:13.5px;flex:1}.price-row{display:flex;align-items:baseline;gap:6px;margin-top:6px}.price{font-size:22px;font-weight:800;letter-spacing:-.03em}.tax{font-size:12px;font-weight:500;color:var(--muted)}
        .cart-form{margin-top:12px;display:grid;grid-template-columns:82px 1fr;gap:8px}.qty{width:100%;border:1px solid var(--border);border-radius:8px;padding:11px 10px;font:inherit}.buy{border:0;text-align:center;text-decoration:none;background:var(--primary);color:#fff;font-weight:700;font-size:14px;padding:12px 16px;border-radius:8px;cursor:pointer;transition:background .18s ease}.buy:hover{background:var(--primary-dark)}
        .info{margin:clamp(32px,5vw,56px) 0;border:1px solid var(--border);border-radius:var(--radius);padding:clamp(20px,3vw,32px);background:var(--tile)}.info h2{margin:0 0 16px;font-size:20px;letter-spacing:-.02em}.info-row{display:grid;grid-template-columns:minmax(120px,.32fr) 1fr;gap:12px;padding:12px 0;border-top:1px solid var(--border);font-size:14px}.info-row:first-of-type{border-top:0}.info-row strong{color:var(--text)}.info-row span{color:var(--muted)}.info-row a{color:var(--primary);text-decoration:none}
        footer{border-top:1px solid var(--border);padding:28px 0 48px;color:var(--muted);font-size:13px}footer .wrap{display:flex;flex-wrap:wrap;gap:8px 20px;align-items:center;justify-content:space-between}footer a{color:var(--muted);text-decoration:none}
        @media(max-width:560px){.topbar{align-items:flex-start;flex-wrap:wrap;padding:12px 16px}.brand{font-size:18px;line-height:1.35}.topnav{width:100%;overflow-x:auto;padding-bottom:2px}.topnav a{flex:0 0 auto;padding:7px 10px;font-size:12px}.cart-form{grid-template-columns:76px 1fr}.info-row{grid-template-columns:1fr;gap:4px}}
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php">SAKURA-NET <span>SHOP</span></a>
        <nav class="topnav">
            <a href="<?php echo sh($site); ?>/">本体サイト</a>
            <a href="<?php echo sh($site); ?>/contact.html">お問い合わせ</a>
            <a href="cart.php">カート（<?php echo cart_count(); ?>）</a>
            <a class="primary" href="#products">商品を見る</a>
        </nav>
    </header>

    <section class="hero">
        <div class="wrap">
            <div class="eyebrow">SAKURA-NET STORE</div>
            <h1>オンラインショップ</h1>
            <p>株式会社さくらねっとのサポート・保守サービスと、UniFi正規取扱いネットワーク機器をまとめて購入できます。複数商品をカートに入れて、Stripeの安全な画面で決済できます。</p>
            <?php if ($added): ?><div class="notice">カートに追加しました。右上のカートから内容を確認できます。</div><?php endif; ?>
        </div>
    </section>

    <nav class="catnav" aria-label="カテゴリ">
        <div class="wrap">
            <?php $navIndex = 0; foreach (array_keys($groups) as $categoryName): ?>
                <a href="#cat-<?php echo $navIndex++; ?>"><?php echo sh($categoryName); ?></a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div id="products">
        <?php $sectionIndex = 0; foreach ($groups as $categoryName => $items): ?>
            <section class="cat" id="cat-<?php echo $sectionIndex++; ?>">
                <div class="wrap">
                    <div class="cat-head">
                        <h2><?php echo sh($categoryName); ?></h2>
                        <span class="cat-count"><?php echo count($items); ?> 商品</span>
                    </div>
                    <div class="grid">
                        <?php foreach ($items as $product): ?>
                            <article class="card">
                                <div class="thumb">
                                    <?php if ((string) $product['image'] !== ''): ?>
                                        <img src="<?php echo sh((string) $product['image']); ?>" alt="<?php echo sh((string) $product['name']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="thumb-ph"><span>SAKURA<b>-NET</b></span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <span class="tag"><?php echo sh((string) $product['category']); ?></span>
                                    <h3><?php echo sh((string) $product['name']); ?></h3>
                                    <p><?php echo sh((string) $product['description']); ?></p>
                                    <div class="price-row">
                                        <span class="price"><?php echo number_format((int) $product['amount']); ?>円 <span class="tax">税込</span></span>
                                    </div>
                                    <form class="cart-form" action="cart.php" method="post">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo sh((string) $product['id']); ?>">
                                        <input class="qty" type="number" name="quantity" min="1" max="99" value="1" aria-label="数量">
                                        <button class="buy" type="submit">カートに追加</button>
                                    </form>
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
            <div class="info-row"><strong>対応決済</strong><span>クレジットカード決済・コンビニ決済（Stripe）。合計金額が30万円を超える場合はカード決済のみになります。</span></div>
            <div class="info-row"><strong>キャンセル・返金方針</strong><span>お支払い後のキャンセル・返金は、商品発送前に限り個別に確認いたします。発送後の返金は原則として承っておりません。</span></div>
            <div class="info-row"><strong>引渡し時期</strong><span>決済確認後、在庫確認のうえ発送または個別に納期をご案内いたします。コンビニ決済はご入金確認後の手配となります。</span></div>
            <div class="info-row"><strong>お問い合わせ</strong><span>株式会社さくらねっと / 受付時間 平日 10:00-19:00（土日祝 休業） / <a href="<?php echo sh($site); ?>/contact.html">お問い合わせフォーム</a></span></div>
            <div class="info-row"><strong>事業者情報</strong><span><a href="tokushoho.html">特定商取引法に基づく表記</a> ／ <a href="<?php echo sh($site); ?>/company.html">会社概要</a></span></div>
        </section>
    </div>

    <footer>
        <div class="wrap">
            <span>&copy; <?php echo date('Y'); ?> 株式会社さくらねっと SAKURA-NET SHOP</span>
            <span>
                <a href="<?php echo sh($site); ?>/">本体サイト</a>
                <a href="tokushoho.html">特定商取引法に基づく表記</a>
                <a href="<?php echo sh($site); ?>/company.html">会社概要</a>
                <a href="<?php echo sh($site); ?>/privacy.html">プライバシー</a>
            </span>
        </div>
    </footer>
</body>
</html>
