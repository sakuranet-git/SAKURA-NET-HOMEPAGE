<?php
declare(strict_types=1);

session_start();
require_once dirname(__DIR__) . '/config.php';

if (empty($_SESSION['checkout_admin'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['checkout_csrf'])) {
    $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
}

$products = checkout_products();
$message = (string) ($_SESSION['checkout_message'] ?? '');
$error = (string) ($_SESSION['checkout_error'] ?? '');
unset($_SESSION['checkout_message'], $_SESSION['checkout_error']);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>商品管理 | SAKURA-NET Checkout</title>
    <style>
        body{margin:0;background:#f6f5f4;color:#37352f;font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7}
        .page{width:min(1120px,calc(100% - 32px));margin:0 auto;padding:44px 0 72px}
        .head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px}
        h1{margin:0;font-size:32px;letter-spacing:-.04em}
        a{color:#0075de}
        .card{background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:20px;overflow-x:auto}
        .notice{border-radius:8px;padding:10px 12px;margin-bottom:14px}
        .ok{background:#eef9f0;border:1px solid #bfe7c8;color:#17632c}
        .ng{background:#fff1f1;border:1px solid #f0b8b8;color:#9b1c1c}
        table{width:100%;border-collapse:collapse;min-width:820px}
        th,td{border-bottom:1px solid rgba(0,0,0,.1);padding:10px;vertical-align:top;text-align:left}
        th{font-size:12px;color:#615d59}
        input,textarea{width:100%;box-sizing:border-box;border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:9px;font:inherit}
        textarea{min-height:62px;resize:vertical}
        .amount{max-width:140px}
        .delete{width:auto}
        .actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px;align-items:center}
        button{border:0;border-radius:4px;background:#0075de;color:#fff;padding:12px 18px;font:inherit;font-weight:700;cursor:pointer}
        .sub{color:#615d59;font-size:13px}
        @media(max-width:640px){.head{display:block}.page{padding-top:28px}.card{padding:14px}}
    </style>
</head>
<body>
    <main class="page">
        <div class="head">
            <div>
                <h1>商品管理</h1>
                <div class="sub">保存後、申込ページへ即時反映されます。</div>
            </div>
            <div><a href="../order.php" target="_blank" rel="noopener">申込ページを開く</a> / <a href="login.php?logout=1">ログアウト</a></div>
        </div>

        <?php if ($message !== ''): ?><div class="notice ok"><?php echo h($message); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="notice ng"><?php echo h($error); ?></div><?php endif; ?>

        <form class="card" method="post" action="save.php">
            <input type="hidden" name="csrf" value="<?php echo h((string) $_SESSION['checkout_csrf']); ?>">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名</th>
                        <th>説明</th>
                        <th>金額</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 0; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><input name="products[<?php echo $index; ?>][id]" value="<?php echo h((string) $product['id']); ?>" required pattern="[A-Za-z0-9_-]+"></td>
                            <td><input name="products[<?php echo $index; ?>][name]" value="<?php echo h((string) $product['name']); ?>" required></td>
                            <td><textarea name="products[<?php echo $index; ?>][description]"><?php echo h((string) $product['description']); ?></textarea></td>
                            <td><input class="amount" name="products[<?php echo $index; ?>][amount]" type="number" min="1" step="1" value="<?php echo h((string) $product['amount']); ?>" required></td>
                            <td><input class="delete" type="checkbox" name="products[<?php echo $index; ?>][delete]" value="1"></td>
                        </tr>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td><input name="products[<?php echo $index; ?>][id]" placeholder="new_product" pattern="[A-Za-z0-9_-]+"></td>
                        <td><input name="products[<?php echo $index; ?>][name]" placeholder="追加商品名"></td>
                        <td><textarea name="products[<?php echo $index; ?>][description]" placeholder="説明"></textarea></td>
                        <td><input class="amount" name="products[<?php echo $index; ?>][amount]" type="number" min="1" step="1" placeholder="3000"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div class="actions">
                <button type="submit">商品を保存</button>
                <span class="sub">IDは英数字、ハイフン、アンダースコアのみ。金額は税込の正の整数です。</span>
            </div>
        </form>
    </main>
</body>
</html>
