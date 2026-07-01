<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$products = checkout_products();
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>お支払い申込 | SAKURA-NET</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f5f4;
            --card: #ffffff;
            --text: #37352f;
            --muted: #615d59;
            --border: rgba(0, 0, 0, 0.1);
            --primary: #0075de;
            --primary-dark: #005fb8;
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
            color: var(--text);
            font-family: Inter, "Noto Sans JP", sans-serif;
            line-height: 1.7;
            overflow-x: hidden;
        }

        .page {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 56px 0 72px;
        }

        .hero {
            display: grid;
            gap: 18px;
            margin-bottom: 28px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(30px, 6vw, 52px);
            letter-spacing: -0.04em;
            line-height: 1.12;
        }

        .lead {
            max-width: 720px;
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 24px;
            align-items: start;
        }

        .products {
            display: grid;
            gap: 16px;
        }

        .product-card,
        .form-card,
        .notice {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .product-card {
            position: relative;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 16px;
            align-items: center;
            padding: 20px;
            cursor: pointer;
            transition: border-color 180ms ease, transform 180ms ease;
        }

        .product-card:hover {
            border-color: rgba(0, 117, 222, 0.35);
            transform: translateY(-2px);
        }

        .product-card input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .product-card > span {
            min-width: 0;
        }

        .product-title {
            display: block;
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .product-desc {
            display: block;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .price {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.04em;
            white-space: nowrap;
        }

        .tax {
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
        }

        .form-card {
            padding: 24px;
        }

        .form-card h2 {
            margin: 0 0 16px;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        label.field {
            display: grid;
            gap: 8px;
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 13px;
            color: var(--text);
            font: inherit;
            background: #fff;
        }

        input:focus {
            border-color: rgba(0, 117, 222, 0.45);
            outline: 3px solid rgba(0, 117, 222, 0.12);
        }

        .button {
            width: 100%;
            border: 0;
            border-radius: 4px;
            padding: 13px 16px;
            background: var(--primary);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: background 180ms ease, transform 180ms ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .notice {
            margin-top: 16px;
            padding: 14px 16px;
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .product-card {
                grid-template-columns: auto 1fr;
            }

            .price {
                grid-column: 2;
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="hero">
            <div class="eyebrow">SAKURA-NET Checkout</div>
            <h1>お支払い内容を選択してください。</h1>
            <p class="lead">カード・コンビニ決済に対応しています。<br>お申込み後、Stripeの安全な決済画面へ移動します。</p>
        </section>

        <form class="layout" action="create_session.php" method="post">
            <section class="products" aria-label="商品選択">
                <?php $first = true; ?>
                <?php foreach ($products as $id => $product): ?>
                    <label class="product-card">
                        <input type="radio" name="product_id" value="<?php echo h($id); ?>" <?php echo $first ? 'checked' : ''; ?> required>
                        <span>
                            <span class="product-title"><?php echo h($product['name']); ?></span>
                            <span class="product-desc"><?php echo h($product['description']); ?></span>
                        </span>
                        <span class="price">
                            <?php echo number_format((int) $product['amount']); ?>円
                            <span class="tax">税込</span>
                        </span>
                    </label>
                    <?php $first = false; ?>
                <?php endforeach; ?>
            </section>

            <aside class="form-card">
                <h2>お客様情報</h2>
                <label class="field">
                    氏名
                    <input type="text" name="customer_name" autocomplete="name" required>
                </label>
                <label class="field">
                    メールアドレス
                    <input type="email" name="customer_email" autocomplete="email" required>
                </label>
                <label class="field">
                    電話番号
                    <input type="tel" name="customer_phone" autocomplete="tel" required>
                </label>
                <button class="button" type="submit">決済画面へ進む</button>
                <div class="notice">30万円を超えるお支払いは、Stripeの仕様に合わせてコンビニ決済を表示せず、カード決済のみになります。</div>
            </aside>
        </form>
    </main>
</body>
</html>
