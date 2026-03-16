<?php
// セッションの有効期限を2時間(7200秒)に設定
ini_set('session.gc_maxlifetime', 7200);
session_set_cookie_params(['lifetime' => 7200]);
session_start();
require_once 'passkey_handler.php';
$passkey = new PasskeyHandler();

// --- 認証ロジック (ID/PASS) ---
$error = '';

// --- パスキー関連のAPIエンドポイント ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    header('Content-Type: application/json');

    switch ($action) {
        case 'getRegistrationChallenge':
            if (!isset($_SESSION['authenticated']))
                exit;
            $challenge = PasskeyHandler::generateChallenge();
            $_SESSION['registration_challenge'] = $challenge;
            echo json_encode(['challenge' => $challenge]);
            exit;

        case 'register':
            if (!isset($_SESSION['authenticated']))
                exit;
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input && isset($input['id'])) {
                $passkey->saveCredential($_SESSION['user_id'], $input);
                echo json_encode(['success' => true]);
            }
            exit;

        case 'getAuthenticationChallenge':
            $challenge = PasskeyHandler::generateChallenge();
            $_SESSION['auth_challenge'] = $challenge;
            // 登録済みの全パスキーIDを返して、ブラウザが適切なものを選択できるようにする
            echo json_encode(['challenge' => $challenge, 'allowCredentials' => $passkey->getAllCredentialIds()]);
            exit;

        case 'verify':
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input && isset($input['id'])) {
                $userId = $passkey->verifyAndLogin($input['id']);
                if ($userId) {
                    $_SESSION['authenticated'] = true;
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['auth_method'] = 'passkey';
                    setcookie('SAKURA_AUTH', 'verified', time() + 1800, '/');
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
            echo json_encode(['success' => false, 'error' => '認証に失敗しました。']);
            exit;
    }
}
if (isset($_POST['login'])) {
    $id = $_POST['id'] ?? '';
    $pass = $_POST['pass'] ?? '';

    // ユーザー指定のID/PASS
    if ($id === 'sakura' && $pass === 'sakura') {
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = 'sakura';
        $_SESSION['auth_method'] = 'password';

        // セキュリティクッキーのセット (.htaccess用)
        setcookie('SAKURA_AUTH', 'verified', time() + 1800, '/');

        header('Location: index.php');
        exit;
    }
    else {
        $error = 'IDまたはパスワードが正しくありません。';
    }
}

// --- Google認証受信の処理 ---
if (isset($_POST['credential'])) {
    $id_token = $_POST['credential'];
    // 本来はベンダーライブラリでJWT検証すべきですが、
    // ここでは簡易的にGoogleの公開検証APIを使用してメールアドレスを確認します
    $verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = @file_get_contents($verify_url);
    $data = json_decode($response, true);

    if ($data && isset($data['email'])) {
        $user_email = $data['email'];
        // 指定されたメールアドレスのみ許可
        if ($user_email === 'akira@sakura-mode.net') {
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user_email;
            $_SESSION['auth_method'] = 'google';
            setcookie('SAKURA_AUTH', 'verified', time() + 1800, '/');
            header('Location: index.php');
            exit;
        }
        else {
            $error = '許可されていないGoogleアカウントです (' . htmlspecialchars($user_email) . ')。';
        }
    }
    else {
        $error = 'Google認証の検証に失敗しました。';
    }
}

// ログアウト処理
if (isset($_GET['logout'])) {
    session_destroy();
    // クッキーも削除
    setcookie('SAKURA_AUTH', '', time() - 3600, '/');
    header('Location: index.php');
    exit;
}

$is_auth = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

// --- 認証済みの場合のデータ取得 (ダッシュボード用) ---
$gw_data = ['board' => [], 'schedule' => []];
if ($is_auth) {
    try {
        $gw_dir = __DIR__ . '/グループウェア/gw_data';
        if (is_dir($gw_dir)) {
            $board_raw = json_decode(@file_get_contents($gw_dir . '/board.json'), true) ?: [];
            $schedule_raw = json_decode(@file_get_contents($gw_dir . '/schedule.json'), true) ?: [];
            
            // 掲示板: 最新3件
            usort($board_raw, fn($a, $b) => strtotime($b['created_at'] ?? '2000-01-01') - strtotime($a['created_at'] ?? '2000-01-01'));
            $gw_data['board'] = array_slice($board_raw, 0, 3);
            
            // スケジュール: 本日以降の3件
            $today = date('Y-m-d');
            $upcoming = array_filter($schedule_raw, fn($s) => ($s['start'] ?? '') >= $today);
            usort($upcoming, fn($a, $b) => strtotime($a['start'] ?? '2100-01-01') - strtotime($b['start'] ?? '2100-01-01'));
            $gw_data['schedule'] = array_slice($upcoming, 0, 3);
        }
    } catch (Exception $e) {
        error_log("Dashboard Data Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAKURA-NET光 システムポータル</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        :root {
            --primary: #e91e63;
            --secondary: #673ab7;
            --accent: #ffeb3b;
            --bg-dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', 'Noto Sans JP', sans-serif;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(233, 30, 99, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(103, 58, 183, 0.15) 0%, transparent 50%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 900px;
            padding: 20px;
            z-index: 10;
        }

        /* --- Login Styles --- */
        .login-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            max-width: 450px;
            margin: 0 auto;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, #ffc107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 32px;
        }
           /* --- Menu Styles --- */
        .portal-header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeIn 1s ease-out;
        }

        .portal-header h1 {
            font-size: clamp(32px, 8vw, 56px);
            font-weight: 800;
            letter-spacing: -2px;
            margin-bottom: 16px;
            line-height: 1.1;
        }

        .portal-header p {
            font-size: clamp(14px, 4vw, 18px);
            color: rgba(255, 255, 255, 0.6);
            max-width: 600px;
            margin: 0 auto;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            perspective: 1000px;
        }

        @media (max-width: 1024px) {
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
        }

        @media (max-width: 640px) {
            .menu-grid { grid-template-columns: 1fr; gap: 20px; }
            .container { padding: 16px; }
        }

        .service-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 48px 32px;
            text-decoration: none;
            color: #fff;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: opacity 0.5s ease;
            z-index: -1;
        }

        .service-card:hover {
            transform: translateY(-12px);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
        }
        
        .service-card:hover::before {
            opacity: 0.1;
        }

        .icon-box {
            width: 96px;
            height: 96px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin-bottom: 28px;
            border: 1px solid var(--glass-border);
            transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .service-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
        }

        .service-card h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .service-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .card-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            border: 1px solid var(--glass-border);
        }
        
        .service-card:hover .card-arrow {
            background: var(--primary);
            border-color: var(--primary);
            transform: translateX(5px);
        }

        .logout-container {
            text-align: center;
            margin-top: 60px;
            padding-bottom: 40px;
        }

        .logout-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-link:hover {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border-color: rgba(244, 67, 54, 0.3);
            transform: scale(1.05);
        }

        /* --- Animations --- */
        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
        }

        input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(233, 30, 99, 0.2);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(233, 30, 99, 0.4);
        }

        .divider {
            margin: 24px 0;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.3);
            font-size: 12px;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 10px;
        }

        .btn-google {
            background: #fff;
            color: #444;
            border: 1px solid #ddd;
        }

        .btn-google:hover {
            background: #f8f8f8;
            border-color: #4285f4;
        }

        .btn-passkey {
            background: rgba(66, 133, 244, 0.1);
            color: #4285f4;
            border: 1px solid rgba(62, 133, 244, 0.4);
        }

        .btn-passkey:hover {
            background: rgba(66, 133, 244, 0.2);
            border-color: #4285f4;
        }
    </style>
</head>
<body>

    <div class="container fade-in">
        
        <?php if (!$is_auth): ?>
            <!-- ログイン画面 -->
            <div class="login-card">
                <div class="logo">SAKURA SYSTEM</div>
                <p class="subtitle">Secure Management Portal</p>
                
                <?php if ($error): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php
    endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="id" placeholder="Username" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input type="password" name="pass" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn-login">ログイン</button>
                </form>

                <div class="divider">または他の方法で認証</div>

                <div class="other-auth" style="display: flex; flex-direction: column; gap: 12px;">
                    <div id="google_btn_wrapper" style="width: 100%; min-height: 40px;"></div>
                    <button class="btn-secondary btn-passkey" onclick="loginPasskey()" style="width: 100%; height: 40px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3m-3-3l2.5-2.5"/></svg>
                        Passkey
                    </button>
                </div>
            </div>

        <?php
else: ?>
            <!-- ポータルメニュー -->
            <div class="portal-header">
                <h1>Welcome Back</h1>
                <p>ご利用になるシステムを選択してください。各システムは強固な認証により保護されています。</p>
            </div>

            <!-- Dashboard Section -->
            <?php if (!empty($gw_data['board']) || !empty($gw_data['schedule'])): ?>
            <div class="dashboard-section fade-in" style="margin-bottom: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="dashboard-card" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 20px;">
                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px; color: #e91e63;"><span style="font-size: 1.2em;">📢</span> 最新の掲示板</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($gw_data['board'] as $post): ?>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <div style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div style="font-size: 11px; color: rgba(255, 255, 255, 0.4); margin-top: 4px;">
                                <?php echo date('m/d H:i', strtotime($post['created_at'])); ?> • <?php echo htmlspecialchars($post['author_name'] ?? '匿名'); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($gw_data['board'])): ?><li style="color: rgba(255, 255, 255, 0.3); font-size: 13px;">新着投稿はありません</li><?php endif; ?>
                    </ul>
                </div>

                <div class="dashboard-card" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 20px;">
                    <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px; color: #4285f4;"><span style="font-size: 1.2em;">📅</span> 予定(本日以降)</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($gw_data['schedule'] as $item): ?>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(66, 133, 244, 0.1); color: #4285f4; padding: 4px 8px; border-radius: 8px; font-size: 11px; font-weight: 700; min-width: 45px; text-align: center;">
                                <?php echo date('m/d', strtotime($item['start'])); ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div style="font-size: 11px; color: rgba(255, 255, 255, 0.4);"><?php echo date('H:i', strtotime($item['start'])); ?> 〜</div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($gw_data['schedule'])): ?><li style="color: rgba(255, 255, 255, 0.3); font-size: 13px;">予定はありません</li><?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="menu-grid">
                <a href="ordersystem.html" class="service-card">
                    <div class="icon-box">👥</div>
                    <h2>顧客管理</h2>
                    <p>契約者情報の閲覧、編集、履歴管理を直感的な操作で行えます。</p>
                    <div class="card-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="sakuranethikari.html" class="service-card">
                    <div class="icon-box">📋</div>
                    <h2>申込み管理</h2>
                    <p>SAKURA-NET光の申込み状況や工事進捗を一元管理できます。</p>
                    <div class="card-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="usagelog.html" class="service-card">
                    <div class="icon-box">💰</div>
                    <h2>利用明細</h2>
                    <p>月々の支払金額や内訳をグラフと一覧で詳しく確認できます。</p>
                    <div class="card-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="calllog.html" class="service-card">
                    <div class="icon-box">📊</div>
                    <h2>通話明細</h2>
                    <p>通話データのインポート、分析、請求額のシミュレーションが可能です。</p>
                    <div class="card-arrow">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="sakura-ai/" class="service-card" style="background: rgba(230, 0, 126, 0.08); border-color: rgba(255, 105, 180, 0.3);">
                    <div class="icon-box">🌸</div>
                    <h2>SAKURA AI</h2>
                    <p>AIアシスタントに質問・相談できます。音声入力にも対応しています。</p>
                    <div class="card-arrow" style="background: linear-gradient(135deg, #e6007e, #ff69b4); color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="sakuramusic/music.html" class="service-card" style="background: rgba(255, 20, 147, 0.08); border-color: rgba(255, 20, 147, 0.3);">
                    <div class="icon-box">🎵</div>
                    <h2>SAKURA MUSIC</h2>
                    <p>Apple Music や YouTube Music と連携して、お気に入りの音楽を楽しめます。</p>
                    <div class="card-arrow" style="background: linear-gradient(135deg, #ff1493, #ff69b4); color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="sakura-os.html" class="service-card" style="background: rgba(233, 30, 99, 0.06); border-color: rgba(233, 30, 99, 0.4); grid-column: 1 / -1;">
                    <div class="icon-box" style="background: linear-gradient(135deg, rgba(233, 30, 99, 0.2), rgba(124, 58, 237, 0.2)); border-color: rgba(233, 30, 99, 0.4);">🌸</div>
                    <h2>SAKURA OS</h2>
                    <p>全システムをデスクトップOS風の統合画面から操作できます。複数アプリをウィンドウで同時に表示・管理できます。</p>
                    <div class="card-arrow" style="background: linear-gradient(135deg, #e91e63, #7c3aed); color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="groupware.html" class="service-card" style="background: rgba(233, 30, 99, 0.05); border-color: rgba(233, 30, 99, 0.3);">
                    <div class="icon-box">📅</div>
                    <h2>グループウェア</h2>
                    <p>スケジュール管理、掲示板、TODOリストなど、社内の情報共有を一元管理します。</p>
                    <div class="card-arrow" style="background: var(--primary); color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <a href="sakura-ai/migrate.php" class="service-card" style="background: rgba(66, 133, 244, 0.05); border-color: rgba(66, 133, 244, 0.3);">
                    <div class="icon-box">🔄</div>
                    <h2>履歴移行ツール</h2>
                    <p>ローカルPCの会話履歴をサーバーに同期します。初回移行や手動同期に使用してください。</p>
                    <div class="card-arrow" style="background: #4285f4; color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </div>
                </a>

                <div class="service-card" onclick="registerPasskey()" style="cursor: pointer; background: rgba(66, 133, 244, 0.05); border-style: dashed;">
                    <div class="icon-box">🔑</div>
                    <h2>パスキー登録</h2>
                    <p>今のデバイス（指紋、顔、暗証番号）を次回からのログインに使えます。</p>
                    <div class="card-arrow" style="background: #4285f4; color: #fff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </div>
                </div>
            </div>

            <div class="logout-container">
                <a href="?logout=1" class="logout-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    ポータルからログアウト
                </a>
            </div>

        <?php
endif; ?>

    </div>

    <script>
        const CLIENT_ID = '42352054977-ll49lo85tan780rupeud49i8r6flphri.apps.googleusercontent.com';

        function handleCredentialResponse(response) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        window.onload = function () {
            if (typeof google !== 'undefined') {
                google.accounts.id.initialize({
                    client_id: CLIENT_ID,
                    callback: handleCredentialResponse,
                    auto_select: false // 勝手にログインしないようにする
                });

                // Googleログインボタンを特定の要素に描画
                const btnWrapper = document.getElementById('google_btn_wrapper');
                if (btnWrapper) {
                    google.accounts.id.renderButton(
                        btnWrapper,
                        { theme: "outline", size: "large", width: "100%", text: "signin_with" }
                    );
                }
                
                // 補助としてワンタップ案内の表示（環境により表示されない）
            }
        };

        async function registerPasskey() {
            try {
                const response = await fetch('?action=getRegistrationChallenge');
                const { challenge } = await response.json();

                const publicKeyCredentialCreationOptions = {
                    challenge: Uint8Array.from(atob(btoa(challenge)), c => c.charCodeAt(0)),
                    rp: { name: "SAKURA Portal", id: window.location.hostname },
                    user: {
                        id: Uint8Array.from("<?php echo $_SESSION['user_id'] ?? 'user'; ?>", c => c.charCodeAt(0)),
                        name: "<?php echo $_SESSION['user_id'] ?? 'user'; ?>",
                        displayName: "SAKURA User"
                    },
                    pubKeyCredParams: [{ alg: -7, type: "public-key" }, { alg: -257, type: "public-key" }],
                    timeout: 60000,
                    attestation: "direct"
                };

                const credential = await navigator.credentials.create({
                    publicKey: publicKeyCredentialCreationOptions
                });

                // サーバーに公開鍵部分（を想定したID）を保存
                await fetch('?action=register', {
                    method: 'POST',
                    body: JSON.stringify({
                        id: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                        name: navigator.userAgent.split(') ')[0].split(' (')[1] || 'Device'
                    })
                });

                alert('パスキーの登録が完了しました！\n次回から「Passkey」ボタンでログインできます。');
            } catch (err) {
                console.error(err);
                alert('パスキーの登録に失敗しました。このブラウザまたはデバイスが対応していない可能性があります。');
            }
        }

        async function loginPasskey() {
            try {
                const response = await fetch('?action=getAuthenticationChallenge');
                const { challenge, allowCredentials } = await response.json();

                if (allowCredentials.length === 0) {
                    alert('登録されているパスキーがありません。先に通常の方法で一度ログインし、「パスキー登録」を行ってください。');
                    return;
                }

                const publicKeyCredentialRequestOptions = {
                    challenge: Uint8Array.from(atob(btoa(challenge)), c => c.charCodeAt(0)),
                    allowCredentials: allowCredentials.map(id => ({
                        id: Uint8Array.from(atob(id), c => c.charCodeAt(0)),
                        type: 'public-key'
                    })),
                    timeout: 60000
                };

                const assertion = await navigator.credentials.get({
                    publicKey: publicKeyCredentialRequestOptions
                });

                const verifyResponse = await fetch('?action=verify', {
                    method: 'POST',
                    body: JSON.stringify({
                        id: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId)))
                    })
                });

                const result = await verifyResponse.json();
                if (result.success) {
                    location.href = 'index.php';
                } else {
                    alert('認証に失敗しました：' + result.error);
                }
            } catch (err) {
                console.error(err);
                alert('パスキー認証に失敗しました。');
            }
        }

        // ========== 30分タイムアウト処理 ==========
        (function() {
            const TIMEOUT_MS = 30 * 60 * 1000; // 30分
            const STORAGE_KEY = 'sakura_last_active';
            let timer;

            function resetTimer() {
                localStorage.setItem(STORAGE_KEY, Date.now());
                clearTimeout(timer);
                timer = setTimeout(doLogout, TIMEOUT_MS);
            }

            function doLogout() {
                localStorage.removeItem(STORAGE_KEY);
                window.location.href = 'index.php?logout=1';
            }

            // ページ表示時\uff08iPhoneを開いた時など\uff09に札の時間をチェック
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    const last = parseInt(localStorage.getItem(STORAGE_KEY) || '0');
                    if (last && (Date.now() - last) > TIMEOUT_MS) {
                        doLogout();
                        return;
                    }
                    resetTimer();
                }
            });

            // マウス・キーボード・タッチ操作でタイマーリセット
            ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'].forEach(function(evt) {
                document.addEventListener(evt, resetTimer, { passive: true });
            });

            // 初期化
            resetTimer();
        })();
    </script>
</body>
</html>
