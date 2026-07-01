<?php
declare(strict_types=1);

session_start();

if (!empty($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!empty($_SESSION['checkout_admin'])) {
    header('Location: products.php');
    exit;
}

$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = (string) ($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (hash_equals('sakura', $username) && hash_equals('sakura', $password)) {
        session_regenerate_id(true);
        $_SESSION['checkout_admin'] = true;
        if (empty($_SESSION['checkout_csrf'])) {
            $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
        }
        header('Location: products.php');
        exit;
    }

    $error = 'ログイン情報が正しくありません。';
}
?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>商品管理ログイン | SAKURA-NET Checkout</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f6f5f4;color:#37352f;font-family:Inter,"Noto Sans JP",sans-serif;line-height:1.7}
        .page{width:calc(100% - 32px);max-width:480px;margin:0 auto;padding:72px 0}
        .card{background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:28px}
        h1{margin:0 0 18px;font-size:28px;letter-spacing:-.03em}
        label{display:grid;gap:8px;margin:0 0 14px;font-size:13px;font-weight:700;color:#615d59}
        input{width:100%;box-sizing:border-box;border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:12px;font:inherit}
        button{width:100%;border:0;border-radius:4px;background:#0075de;color:#fff;padding:12px 16px;font:inherit;font-weight:700;cursor:pointer}
        .error{background:#fff1f1;border:1px solid #f0b8b8;border-radius:8px;padding:10px 12px;color:#9b1c1c;margin-bottom:14px}
    </style>
</head>
<body>
    <main class="page">
        <section class="card">
            <h1>商品管理ログイン</h1>
            <?php if ($error !== ''): ?><div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <form method="post" action="login.php">
                <label>ユーザー名<input type="text" name="username" autocomplete="username" required></label>
                <label>パスワード<input type="password" name="password" autocomplete="current-password" required></label>
                <button type="submit">ログイン</button>
            </form>
        </section>
    </main>
</body>
</html>
