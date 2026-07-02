<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function cart_raw(): array
{
    return is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
}

function cart_set(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cart_items(array $products): array
{
    $items = [];
    foreach (cart_raw() as $id => $quantity) {
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

function cart_count(array $items): int
{
    $count = 0;
    foreach ($items as $item) {
        $count += (int) $item['quantity'];
    }
    return $count;
}

function cart_total(array $items): int
{
    $total = 0;
    foreach ($items as $item) {
        $total += (int) $item['subtotal'];
    }
    return $total;
}

$products = checkout_products();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $cart = cart_raw();

    if ($action === 'add') {
        $id = (string) ($_POST['product_id'] ?? '');
        $quantity = max(1, min(99, (int) ($_POST['quantity'] ?? 1)));
        if (isset($products[$id])) {
            $cart[$id] = min(99, ((int) ($cart[$id] ?? 0)) + $quantity);
            cart_set($cart);
        }
        header('Location: index.php?cart_added=1', true, 303);
        exit;
    }

    if ($action === 'update') {
        $submitted = $_POST['quantities'] ?? [];
        $next = [];
        if (is_array($submitted)) {
            foreach ($submitted as $id => $quantity) {
                $id = (string) $id;
                $quantity = (int) $quantity;
                if ($quantity >= 1 && $quantity <= 99 && isset($products[$id])) {
                    $next[$id] = $quantity;
                }
            }
        }
        cart_set($next);
        header('Location: cart.php?updated=1', true, 303);
        exit;
    }

    if ($action === 'remove') {
        $id = (string) ($_POST['product_id'] ?? '');
        unset($cart[$id]);
        cart_set($cart);
        header('Location: cart.php?updated=1', true, 303);
        exit;
    }

    if ($action === 'clear') {
        cart_set([]);
        header('Location: cart.php?updated=1', true, 303);
        exit;
    }

    header('Location: cart.php', true, 303);
    exit;
}

$items = cart_items($products);
$count = cart_count($items);
$total = cart_total($items);
$updated = (string) ($_GET['updated'] ?? '') === '1';
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>カート | SAKURA-NET SHOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#f6f5f4;--card:#fff;--text:#16181d;--muted:#6a7180;--border:#e6e8ec;--primary:#006fff;--primary-dark:#0059cc;--radius:14px}
        *{box-sizing:border-box}body{margin:0;background:linear-gradient(180deg,#fff 0%,var(--bg) 100%);color:var(--text);font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7;-webkit-font-smoothing:antialiased}.page{width:min(1080px,calc(100% - 32px));margin:0 auto;padding:44px 0 72px}.top{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:28px}.brand{text-decoration:none;font-weight:800;font-size:18px}.brand span{color:var(--primary)}.nav{display:flex;gap:10px;flex-wrap:wrap}.nav a{color:var(--muted);text-decoration:none;font-size:13px;font-weight:700;padding:8px 12px;border-radius:999px}.nav a:hover{background:#fff;color:var(--text)}
        .hero{margin-bottom:24px}.eyebrow{color:var(--primary);font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}h1{margin:10px 0;font-size:clamp(30px,6vw,48px);letter-spacing:-.04em;line-height:1.1}.lead{margin:0;color:var(--muted)}.notice{margin-top:14px;display:inline-flex;border:1px solid rgba(0,111,255,.2);background:rgba(0,111,255,.07);color:var(--primary);border-radius:999px;padding:8px 13px;font-size:13px;font-weight:800}
        .layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start}.card,.summary,.empty{background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}.cart-list{display:grid;gap:14px}.item{display:grid;grid-template-columns:110px minmax(0,1fr) auto;gap:16px;padding:16px}.thumb{height:92px;border-radius:10px;background:#f6f6f8;display:flex;align-items:center;justify-content:center;overflow:hidden}.thumb img{max-width:100%;max-height:100%;object-fit:contain}.thumb-ph{color:#b9c0cc;font-weight:800}.thumb-ph b{color:var(--primary)}.name{font-weight:800;font-size:16px;overflow-wrap:anywhere}.desc{margin:4px 0;color:var(--muted);font-size:13px;overflow-wrap:anywhere}.unit{color:var(--muted);font-size:13px}.qty-row{display:flex;align-items:center;gap:8px;margin-top:10px}.qty{width:86px;border:1px solid var(--border);border-radius:8px;padding:10px;font:inherit}.remove{border:0;background:transparent;color:#cc3344;font:inherit;font-weight:700;cursor:pointer;padding:0}.subtotal{font-weight:900;font-size:18px;white-space:nowrap}
        .summary{padding:20px;position:sticky;top:20px}.summary h2{margin:0 0 14px;font-size:20px}.sum-row{display:flex;justify-content:space-between;gap:12px;border-top:1px solid var(--border);padding:12px 0;color:var(--muted)}.sum-row.total{color:var(--text);font-weight:900;font-size:20px}.actions{display:grid;gap:10px;margin-top:14px}.button{border:0;border-radius:8px;background:var(--primary);color:#fff;text-decoration:none;text-align:center;padding:13px 16px;font:inherit;font-weight:800;cursor:pointer}.button:hover{background:var(--primary-dark)}.button.secondary{background:#fff;color:var(--text);border:1px solid var(--border)}.button.danger{background:#fff;color:#cc3344;border:1px solid #f0c8cf}.empty{padding:28px}.empty p{color:var(--muted)}
        @media(max-width:820px){.layout{grid-template-columns:1fr}.summary{position:static}.item{grid-template-columns:88px minmax(0,1fr)}.subtotal{grid-column:2}.top{display:block}.nav{margin-top:10px}}
        @media(max-width:520px){.page{width:min(100% - 24px,1080px);padding-top:28px}.item{grid-template-columns:1fr}.thumb{height:180px}.subtotal{grid-column:auto}.qty-row{justify-content:space-between}}
    </style>
</head>
<body>
    <main class="page">
        <div class="top">
            <a class="brand" href="index.php">SAKURA-NET <span>SHOP</span></a>
            <nav class="nav">
                <a href="index.php">商品一覧</a>
                <a href="cart.php">カート（<?php echo $count; ?>）</a>
            </nav>
        </div>

        <section class="hero">
            <div class="eyebrow">Shopping Cart</div>
            <h1>カート</h1>
            <p class="lead">数量を確認して、まとめて購入手続きへ進めます。</p>
            <?php if ($updated): ?><div class="notice">カートを更新しました。</div><?php endif; ?>
        </section>

        <?php if ($items === []): ?>
            <section class="empty">
                <h2>カートは空です</h2>
                <p>商品一覧から必要な商品をカートへ追加してください。</p>
                <a class="button" href="index.php">商品一覧へ戻る</a>
            </section>
        <?php else: ?>
            <div class="layout">
                <form class="cart-list" action="cart.php" method="post">
                    <input type="hidden" name="action" value="update">
                    <?php foreach ($items as $item): ?>
                        <?php $product = $item['product']; ?>
                        <article class="card item">
                            <div class="thumb">
                                <?php if ((string) $product['image'] !== ''): ?>
                                    <img src="<?php echo h((string) $product['image']); ?>" alt="<?php echo h((string) $product['name']); ?>">
                                <?php else: ?>
                                    <div class="thumb-ph">SAKURA<b>-NET</b></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="name"><?php echo h((string) $product['name']); ?></div>
                                <p class="desc"><?php echo h((string) $product['description']); ?></p>
                                <div class="unit">単価 <?php echo number_format((int) $product['amount']); ?>円（税込）</div>
                                <div class="qty-row">
                                    <label>数量 <input class="qty" type="number" name="quantities[<?php echo h($item['id']); ?>]" min="1" max="99" value="<?php echo (int) $item['quantity']; ?>"></label>
                                    <button class="remove" type="submit" name="remove_hint" value="<?php echo h($item['id']); ?>" formaction="cart.php" formmethod="post" onclick="event.preventDefault(); const f=document.createElement('form'); f.method='post'; f.action='cart.php'; f.innerHTML='<input name=&quot;action&quot; value=&quot;remove&quot;><input name=&quot;product_id&quot; value=&quot;<?php echo h($item['id']); ?>&quot;>'; document.body.appendChild(f); f.submit();">削除</button>
                                </div>
                            </div>
                            <div class="subtotal"><?php echo number_format((int) $item['subtotal']); ?>円</div>
                        </article>
                    <?php endforeach; ?>
                    <button class="button secondary" type="submit">数量を更新</button>
                </form>

                <aside class="summary">
                    <h2>ご注文内容</h2>
                    <div class="sum-row"><span>商品点数</span><strong><?php echo $count; ?>点</strong></div>
                    <div class="sum-row total"><span>合計</span><strong><?php echo number_format($total); ?>円</strong></div>
                    <p class="lead">合計30万円を超える場合、決済方法はカードのみになります。</p>
                    <div class="actions">
                        <a class="button" href="order.php">レジに進む</a>
                        <a class="button secondary" href="index.php">買い物を続ける</a>
                        <form action="cart.php" method="post">
                            <input type="hidden" name="action" value="clear">
                            <button class="button danger" type="submit">カートを空にする</button>
                        </form>
                    </div>
                </aside>
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
