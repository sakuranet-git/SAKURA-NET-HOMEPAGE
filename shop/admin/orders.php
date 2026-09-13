<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['checkout_admin'])) {
    header('Location: login.php');
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function orders_path(): string
{
    return dirname(__DIR__) . '/data/orders.json';
}

function load_orders(): array
{
    $path = orders_path();
    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return [];
    }

    $orders = array_is_list($decoded) ? $decoded : array_values($decoded);
    return array_reverse($orders);
}

function format_amount(int|float|string $amount): string
{
    return number_format((int) $amount) . '円';
}

function item_summary(array $items): string
{
    $parts = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = (string) ($item['name'] ?? '');
        $quantity = (int) ($item['quantity'] ?? 0);
        if ($name !== '' && $quantity > 0) {
            $parts[] = $name . ' x ' . $quantity;
        }
    }

    return implode(' / ', $parts);
}

$orders = load_orders();
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>注文履歴 | SAKURA-NET Shop</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f6f5f4;color:#37352f;font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7}
        .page{width:min(1320px,calc(100% - 32px));margin:0 auto;padding:44px 0 72px}
        .head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px}
        h1{margin:0;font-size:32px;letter-spacing:-.04em}
        a{color:#0075de}
        .sub{color:#615d59;font-size:13px}
        .card{background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:20px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:1180px}
        th,td{border-bottom:1px solid rgba(0,0,0,.1);padding:10px;vertical-align:top;text-align:left}
        th{font-size:12px;color:#615d59;white-space:nowrap}
        .mono{font-family:ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",monospace;font-size:12px}
        .pill{display:inline-flex;border-radius:999px;background:#eef4ff;color:#0059cc;padding:2px 8px;font-size:12px;font-weight:700}
        .empty{padding:28px;color:#615d59}
        @media(max-width:640px){.head{display:block}.page{padding-top:28px}.card{padding:14px}}
    </style>
</head>
<body>
    <main class="page">
        <div class="head">
            <div>
                <h1>注文履歴</h1>
                <div class="sub">Stripe Checkout作成時点の注文控えです。支払い完了状態はStripe管理画面と照合してください。</div>
            </div>
            <div><a href="products.php">商品管理</a> / <a href="../index.php" target="_blank" rel="noopener">ショップを見る</a> / <a href="login.php?logout=1">ログアウト</a></div>
        </div>

        <section class="card">
            <?php if ($orders === []): ?>
                <div class="empty">注文履歴はまだありません。</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>日時</th>
                            <th>注文番号</th>
                            <th>購入者</th>
                            <th>確認情報</th>
                            <th>商品</th>
                            <th>合計</th>
                            <th>状態</th>
                            <th>Stripe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $customer = is_array($order['customer'] ?? null) ? $order['customer'] : [];
                            $items = is_array($order['items'] ?? null) ? $order['items'] : [];
                            $sessionId = (string) ($order['stripe_session_id'] ?? '');
                            $paymentIntent = (string) ($order['stripe_payment_intent'] ?? '');
                            ?>
                            <tr>
                                <td><?php echo h((string) ($order['created_at'] ?? '')); ?></td>
                                <td class="mono"><?php echo h((string) ($order['order_id'] ?? '')); ?></td>
                                <td>
                                    <strong><?php echo h((string) ($customer['name'] ?? '')); ?></strong><br>
                                    <?php echo h((string) ($customer['email'] ?? '')); ?><br>
                                    <?php echo h((string) ($customer['phone'] ?? '')); ?>
                                </td>
                                <td><?php echo nl2br(h((string) ($customer['reference'] ?? ''))); ?></td>
                                <td><?php echo h(item_summary($items)); ?></td>
                                <td><strong><?php echo h(format_amount($order['total'] ?? 0)); ?></strong></td>
                                <td>
                                    <span class="pill"><?php echo h((string) ($order['payment_status'] ?? 'created')); ?></span><br>
                                    <span class="sub"><?php echo h((string) ($order['checkout_status'] ?? '')); ?></span>
                                </td>
                                <td class="mono">
                                    Session: <?php echo h($sessionId); ?><br>
                                    PaymentIntent: <?php echo h($paymentIntent); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
