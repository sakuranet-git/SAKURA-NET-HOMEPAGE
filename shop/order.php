<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/checkout_rules.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
            'quantity' => $quantity,
            'subtotal' => (int) $product['amount'] * $quantity,
        ];
    }
    return $items;
}

$products = checkout_products();
$items = checkout_cart_items($products);
$total = array_sum(array_map(static fn(array $item): int => (int) $item['subtotal'], $items));
$count = array_sum(array_map(static fn(array $item): int => (int) $item['quantity'], $items));
$hasUnavailable = false;
foreach ($items as $item) {
    if (!checkout_is_product_available($item['product']) || !shop_is_checkout_enabled_product($item['product'])) {
        $hasUnavailable = true;
        break;
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>購入手続き | SAKURA-NET SHOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#f6f5f4;--card:#fff;--text:#16181d;--muted:#6a7180;--border:#e6e8ec;--primary:#006fff;--primary-dark:#0059cc;--radius:14px}
        *{box-sizing:border-box}body{margin:0;background:linear-gradient(180deg,#fff 0%,var(--bg) 100%);color:var(--text);font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7}.page{width:min(1080px,calc(100% - 32px));margin:0 auto;padding:44px 0 72px}.top{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:28px}.brand{text-decoration:none;font-weight:800;font-size:18px}.brand span{color:var(--primary)}.nav{display:flex;gap:10px;flex-wrap:wrap}.nav a{color:var(--muted);text-decoration:none;font-size:13px;font-weight:700;padding:8px 12px;border-radius:999px}.nav a:hover{background:#fff;color:var(--text)}
        .eyebrow{color:var(--primary);font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}h1{margin:10px 0;font-size:clamp(30px,6vw,48px);letter-spacing:-.04em;line-height:1.1}.lead{margin:0;color:var(--muted)}
        .layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:24px;align-items:start;margin-top:24px}.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}.summary{padding:20px}.summary h2,.form-card h2{margin:0 0 16px;font-size:20px}.item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:13px 0;border-top:1px solid var(--border)}.item:first-of-type{border-top:0}.item-name{font-weight:800;overflow-wrap:anywhere}.item-meta{color:var(--muted);font-size:13px}.item-subtotal{font-weight:900;white-space:nowrap}.total{display:flex;justify-content:space-between;gap:12px;border-top:1px solid var(--border);padding-top:16px;margin-top:12px;font-size:22px;font-weight:900}.alert{margin-top:14px;border:1px solid #f0c8cf;background:#fff1f1;color:#9b1c1c;border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700}
        .form-card{padding:22px}label.field{display:grid;gap:8px;margin-bottom:14px;color:var(--muted);font-size:13px;font-weight:800}input[type=text],input[type=email],input[type=tel]{width:100%;border:1px solid var(--border);border-radius:8px;padding:12px 13px;color:var(--text);font:inherit;background:#fff}input:focus{border-color:rgba(0,111,255,.45);outline:3px solid rgba(0,111,255,.12)}.member-panel{margin:18px 0;border:1px solid rgba(0,111,255,.2);background:rgba(0,111,255,.06);border-radius:12px;padding:14px}.member-panel strong{display:block;margin-bottom:4px;color:var(--text);font-size:14px}.member-panel p{margin:0 0 12px;color:var(--muted);font-size:13px}.check-field{display:flex;gap:10px;align-items:flex-start;margin:12px 0 0;color:var(--text);font-size:13px;font-weight:800}.check-field input{margin-top:4px;inline-size:18px;block-size:18px;flex:0 0 auto}.button{display:inline-flex;align-items:center;justify-content:center;width:100%;border:0;border-radius:8px;background:var(--primary);color:#fff;text-decoration:none;padding:13px 16px;font:inherit;font-weight:800;cursor:pointer}.button:hover{background:var(--primary-dark)}.button.secondary{background:#fff;color:var(--text);border:1px solid var(--border);margin-top:10px}.button[disabled]{cursor:not-allowed;opacity:.55;background:#aeb6c3}.notice{margin-top:14px;color:var(--muted);font-size:13px}.empty{margin-top:24px;padding:28px}.empty p{color:var(--muted)}
        @media(max-width:820px){.layout{grid-template-columns:1fr}.top{display:block}.nav{margin-top:10px}}@media(max-width:520px){.page{width:min(100% - 24px,1080px);padding-top:28px}.item{grid-template-columns:1fr}.item-subtotal{text-align:left}}
    </style>
</head>
<body>
    <main class="page">
        <div class="top">
            <a class="brand" href="index.php">SAKURA-NET <span>SHOP</span></a>
            <nav class="nav">
                <a href="index.php">商品一覧</a>
                <a href="cart.php">カート（<?php echo (int) $count; ?>）</a>
            </nav>
        </div>

        <section>
            <div class="eyebrow">Checkout</div>
            <h1>購入手続き</h1>
            <p class="lead">ご注文内容を確認し、お客様情報を入力してください。本ショップのオンライン決済はサポート・保守カテゴリのみ対応しています。</p>
        </section>

        <?php if ($items === []): ?>
            <section class="card empty">
                <h2>カートは空です</h2>
                <p>商品をカートに追加してから購入手続きへ進んでください。</p>
                <a class="button" href="index.php">商品一覧へ戻る</a>
            </section>
        <?php else: ?>
            <div class="layout">
                <section class="card summary">
                    <h2>ご注文内容</h2>
                    <?php foreach ($items as $item): ?>
                        <?php $product = $item['product']; ?>
                        <div class="item">
                            <div>
                                <div class="item-name"><?php echo h((string) $product['name']); ?></div>
                                <div class="item-meta"><?php echo number_format((int) $product['amount']); ?>円 × <?php echo (int) $item['quantity']; ?></div>
                            </div>
                            <div class="item-subtotal"><?php echo number_format((int) $item['subtotal']); ?>円</div>
                        </div>
                    <?php endforeach; ?>
                    <div class="total"><span>合計</span><span><?php echo number_format((int) $total); ?>円</span></div>
                    <?php if ($hasUnavailable): ?><div class="alert">在庫切れ・近日入荷・オンライン決済対象外の商品が含まれています。カートへ戻って対象商品を削除してください。</div><?php endif; ?>
                    <a class="button secondary" href="cart.php">カートへ戻る</a>
                </section>

                <form class="card form-card" action="create_session.php" method="post">
                    <h2>お客様情報</h2>
                    <label class="field">氏名<input type="text" name="customer_name" autocomplete="name" required></label>
                    <label class="field">メールアドレス<input type="email" name="customer_email" autocomplete="email" required></label>
                    <label class="field">電話番号<input type="tel" name="customer_phone" autocomplete="tel" required></label>
                    <div class="member-panel">
                        <strong>既存顧客・取引先確認</strong>
                        <p>契約者名、契約ID、請求書番号、当社担当者名など、さくらねっととのお取引を確認できる情報を入力してください。確認できないご注文、対象外のお客様によるご注文はキャンセルまたは返金対応となる場合があります。</p>
                        <label class="field">確認情報<input type="text" name="customer_reference" autocomplete="off" placeholder="例：契約者名 / 請求書番号 / 当社担当者名" required></label>
                        <label class="check-field"><input type="checkbox" name="customer_confirm" value="1" required><span>私は、さくらねっとサービスを利用中、または当社より案内を受けた既存顧客・取引先です。</span></label>
                    </div>
                    <button class="button" type="submit" <?php echo $hasUnavailable ? 'disabled' : ''; ?>>決済画面へ進む</button>
                    <div class="notice">合計30万円を超える場合、コンビニ決済は表示されずカード決済のみになります。</div>
                </form>
            </div>
        <?php endif; ?>
    </main>
<footer style="border-top:1px solid #e6e8ec;margin-top:32px;padding:20px 16px 40px;color:#6a7180;font-size:13px;text-align:center;font-family:Inter,'Noto Sans JP',sans-serif;">
<a href="tokushoho.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">特定商取引法に基づく表記</a>
<a href="https://sakuranet-co.jp/company.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">会社概要</a>
<a href="https://sakuranet-co.jp/privacy.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">プライバシーポリシー</a>
<a href="https://sakuranet-co.jp/contact.html" style="color:#6a7180;text-decoration:none;margin:0 8px;">お問い合わせ</a>
</footer>
    </body>
</html>
