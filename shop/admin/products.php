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

$categories = [];
foreach ($products as $product) {
    $category = trim((string) ($product['category'] ?? ''));
    if ($category !== '') {
        $categories[$category] = true;
    }
}

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
    <title>商品管理 | SAKURA-NET Shop</title>
    <style>
        body{margin:0;background:#f6f5f4;color:#37352f;font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7}
        .page{width:min(1320px,calc(100% - 32px));margin:0 auto;padding:44px 0 72px}
        .head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px}
        h1{margin:0;font-size:32px;letter-spacing:-.04em}
        a{color:#0075de}
        .card{background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:20px;overflow-x:auto}
        .notice{border-radius:8px;padding:10px 12px;margin-bottom:14px}
        .ok{background:#eef9f0;border:1px solid #bfe7c8;color:#17632c}
        .ng{background:#fff1f1;border:1px solid #f0b8b8;color:#9b1c1c}
        table{width:100%;border-collapse:collapse;min-width:1280px}
        th,td{border-bottom:1px solid rgba(0,0,0,.1);padding:10px;vertical-align:top;text-align:left}
        th{font-size:12px;color:#615d59;white-space:nowrap}
        input,textarea{width:100%;box-sizing:border-box;border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:9px;font:inherit;background:#fff}
        input[type="file"]{padding:7px;background:#fafafa}
        textarea{min-height:72px;resize:vertical}
        .amount{max-width:120px}
        .category{min-width:150px}
        .image-url{min-width:220px}
        .delete{width:auto}
        .thumb{width:88px;height:64px;border:1px solid rgba(0,0,0,.1);border-radius:8px;background:#f6f5f4;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:8px;color:#615d59;font-size:12px}
        .thumb img{width:100%;height:100%;object-fit:contain}
        .actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px;align-items:center}
        button{border:0;border-radius:4px;background:#0075de;color:#fff;padding:12px 18px;font:inherit;font-weight:700;cursor:pointer}
        .sub{color:#615d59;font-size:13px}
        .hint{display:block;margin-top:6px;color:#615d59;font-size:12px;line-height:1.5}
        @media(max-width:640px){.head{display:block}.page{padding-top:28px}.card{padding:14px}}
    </style>
</head>
<body>
    <main class="page">
        <div class="head">
            <div>
                <h1>商品管理</h1>
                <div class="sub">保存後、申込ページとショップ画面へ即時反映されます。画像はURL入力またはファイルアップロードに対応しています。</div>
            </div>
            <div><a href="../order.php" target="_blank" rel="noopener">申込ページを開く</a> / <a href="../index.php" target="_blank" rel="noopener">ショップを見る</a> / <a href="login.php?logout=1">ログアウト</a></div>
        </div>

        <?php if ($message !== ''): ?><div class="notice ok"><?php echo h($message); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="notice ng"><?php echo h($error); ?></div><?php endif; ?>

        <form class="card" method="post" action="save.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo h((string) $_SESSION['checkout_csrf']); ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
            <datalist id="category-list">
                <?php foreach (array_keys($categories) as $category): ?>
                    <option value="<?php echo h($category); ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名</th>
                        <th>説明</th>
                        <th>金額</th>
                        <th>カテゴリ</th>
                        <th>画像</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $index = 0; ?>
                    <?php foreach ($products as $product): ?>
                        <?php $image = trim((string) ($product['image'] ?? '')); ?>
                        <?php $previewSrc = preg_match('/^https?:\/\//i', $image) ? $image : '../' . $image; ?>
                        <tr>
                            <td><input name="products[<?php echo $index; ?>][id]" value="<?php echo h((string) $product['id']); ?>" required pattern="[A-Za-z0-9_-]+"></td>
                            <td><input name="products[<?php echo $index; ?>][name]" value="<?php echo h((string) $product['name']); ?>" required></td>
                            <td><textarea name="products[<?php echo $index; ?>][description]"><?php echo h((string) $product['description']); ?></textarea></td>
                            <td><input class="amount" name="products[<?php echo $index; ?>][amount]" type="number" min="1" step="1" value="<?php echo h((string) $product['amount']); ?>" required></td>
                            <td><input class="category" list="category-list" name="products[<?php echo $index; ?>][category]" value="<?php echo h((string) ($product['category'] ?? 'Products')); ?>"></td>
                            <td>
                                <div class="thumb">
                                    <?php if ($image !== ''): ?>
                                        <img src="<?php echo h($previewSrc); ?>" alt="">
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </div>
                                <input class="image-url" name="products[<?php echo $index; ?>][image]" type="text" value="<?php echo h($image); ?>" placeholder="uploads/example.webp または https://...">
                                <input name="products[<?php echo $index; ?>][image_file]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <span class="hint">両方指定した場合はアップロード画像を優先します。未指定なら既存画像を保持します。</span>
                            </td>
                            <td><input class="delete" type="checkbox" name="products[<?php echo $index; ?>][delete]" value="1"></td>
                        </tr>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td><input name="products[<?php echo $index; ?>][id]" placeholder="new_product" pattern="[A-Za-z0-9_-]+"></td>
                        <td><input name="products[<?php echo $index; ?>][name]" placeholder="追加商品名"></td>
                        <td><textarea name="products[<?php echo $index; ?>][description]" placeholder="説明"></textarea></td>
                        <td><input class="amount" name="products[<?php echo $index; ?>][amount]" type="number" min="1" step="1" placeholder="3000"></td>
                        <td><input class="category" list="category-list" name="products[<?php echo $index; ?>][category]" placeholder="カテゴリ"></td>
                        <td>
                            <div class="thumb">New</div>
                            <input class="image-url" name="products[<?php echo $index; ?>][image]" type="text" placeholder="uploads/example.webp または https://...">
                            <input name="products[<?php echo $index; ?>][image_file]" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="hint">新規追加もURLまたはファイルアップロードを利用できます。</span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div class="actions">
                <button type="submit">商品を保存</button>
                <span class="sub">IDは英数字、ハイフン、アンダースコアのみ。画像ファイルは jpg / png / webp、5MB以下です。</span>
            </div>
        </form>
    </main>
</body>
</html>
