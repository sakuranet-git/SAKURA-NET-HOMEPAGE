<?php
ini_set('session.gc_maxlifetime', 600);
session_set_cookie_params(['lifetime' => 600]);
session_start();
if (empty($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /system/index.php');
    exit;
}

define('SYNC_FILE', __DIR__ . '/chat_data.json');

$message = '';
$messageType = '';

// サーバー→ローカル用JSONダウンロード
if (isset($_GET['download'])) {
    $data = file_exists(SYNC_FILE) ? file_get_contents(SYNC_FILE) : '[]';
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="chat_data.json"');
    echo $data;
    exit;
}

// ローカル→サーバー アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jsonfile'])) {
    $file = $_FILES['jsonfile'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($file['tmp_name']);
        $data = json_decode($content, true);
        if ($data === null) {
            $message = '❌ JSONファイルの形式が正しくありません。';
            $messageType = 'err';
        } else {
            // マージモードの場合
            $mode = $_POST['mode'] ?? 'overwrite';
            if ($mode === 'merge' && file_exists(SYNC_FILE)) {
                $serverData = json_decode(file_get_contents(SYNC_FILE), true) ?? [];
                $mergedMap = [];
                foreach (array_merge($serverData, $data) as $conv) {
                    if (empty($conv['id'])) continue;
                    $id = (string)$conv['id'];
                    $ex = $mergedMap[$id] ?? null;
                    $newLen = count($conv['messages'] ?? []);
                    $exLen = $ex ? count($ex['messages'] ?? []) : -1;
                    if (!$ex || $newLen > $exLen) $mergedMap[$id] = $conv;
                }
                ksort($mergedMap);
                $data = array_values($mergedMap);
            }
            $result = file_put_contents(SYNC_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
            if ($result !== false) {
                $message = '✅ ' . count($data) . '件の履歴をサーバーに保存しました！';
                $messageType = 'ok';
            } else {
                $message = '❌ ファイルへの書き込みに失敗しました。';
                $messageType = 'err';
            }
        }
    } else {
        $message = '❌ アップロード失敗 (error: ' . $file['error'] . ')';
        $messageType = 'err';
    }
}

// サーバーの現在の件数を取得
$serverCount = 0;
if (file_exists(SYNC_FILE)) {
    $serverData = json_decode(file_get_contents(SYNC_FILE), true);
    $serverCount = is_array($serverData) ? count($serverData) : 0;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SAKURA AI - 履歴移行ツール</title>
<style>
body{font-family:sans-serif;background:#1a0010;color:#fff;padding:40px;max-width:640px;margin:0 auto}
h1{color:#ffb6d9;margin-bottom:4px}
.subtitle{color:rgba(255,182,217,0.6);font-size:13px;margin-bottom:28px}
.card{background:rgba(255,255,255,0.06);border:1px solid rgba(255,105,180,0.3);border-radius:14px;padding:24px;margin-bottom:20px}
.card h2{font-size:15px;color:#ffb6d9;margin-bottom:4px}
.card .desc{font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:14px}
.step{font-size:13px;color:rgba(255,255,255,0.8);margin-bottom:6px}
.step span{color:#ff69b4;font-weight:bold}
.server-status{background:rgba(255,105,180,0.1);border:1px solid rgba(255,105,180,0.2);border-radius:8px;padding:10px 14px;font-size:13px;color:#ffb6d9;margin-bottom:16px}
button,.dl-btn{background:linear-gradient(135deg,#e6007e,#ff69b4);border:none;border-radius:8px;color:#fff;padding:11px 20px;font-size:14px;cursor:pointer;width:100%;margin-top:8px;display:block;text-align:center;box-sizing:border-box;text-decoration:none}
button:hover,.dl-btn:hover{opacity:0.85}
.btn-blue{background:linear-gradient(135deg,#1a73e8,#4285f4)}
.btn-green{background:linear-gradient(135deg,#0a8a4a,#34a853)}
.msg{margin-top:14px;padding:12px;border-radius:8px;font-size:13px}
.ok{background:rgba(0,200,100,0.2);border:1px solid rgba(0,200,100,0.4);color:#7fffb0}
.err{background:rgba(255,50,50,0.2);border:1px solid rgba(255,50,50,0.4);color:#ffb0b0}
label.file-label{background:rgba(255,255,255,0.08);border:1px dashed rgba(255,105,180,0.4);border-radius:8px;color:#ffb6d9;padding:11px 20px;font-size:14px;cursor:pointer;width:100%;margin-top:8px;display:block;text-align:center;box-sizing:border-box}
label.file-label:hover{background:rgba(255,105,180,0.15)}
input[type=file]{display:none}
#filename{margin-top:6px;font-size:11px;color:rgba(255,182,217,0.6);text-align:center}
.radio-group{display:flex;gap:12px;margin:10px 0}
.radio-group label{font-size:13px;color:rgba(255,255,255,0.8);display:flex;align-items:center;gap:6px;cursor:pointer}
.back-link{display:inline-block;margin-bottom:20px;color:rgba(255,182,217,0.6);font-size:13px;text-decoration:none}
.back-link:hover{color:#ffb6d9}
</style>
</head>
<body>

<a href="/system/index.php" class="back-link">← ポータルに戻る</a>

<h1>🔄 SAKURA AI 履歴移行ツール</h1>
<p class="subtitle">ローカル ⇔ サーバー間の会話履歴を同期します</p>

<div class="server-status">
    📊 サーバーの現在の履歴：<strong><?= $serverCount ?>件</strong>
</div>

<!-- ========== A: ローカル → サーバー ========== -->
<div class="card">
    <h2>📤 ローカル → サーバーにアップロード</h2>
    <p class="desc">ローカルPCで作成した会話履歴をサーバーに反映します。</p>

    <div class="step">① <span>ローカルPC</span> で SAKURA AI を開き、📥ボタン → JSON でエクスポート</div>
    <div class="step">　または <span>http://192.168.7.120:8080/sakura-ai/migrate.php</span> の「ダウンロード」ボタンを使用</div>
    <div class="step">② ダウンロードしたファイルを下で選択してアップロード</div>

    <?php if ($message): ?>
        <div class="msg <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="radio-group">
            <label><input type="radio" name="mode" value="overwrite" checked> 上書き（サーバーを完全に置き換え）</label>
            <label><input type="radio" name="mode" value="merge"> マージ（両方を合わせる）</label>
        </div>
        <label class="file-label" for="jsonfile">📁 chat_data.json を選択</label>
        <input type="file" id="jsonfile" name="jsonfile" accept=".json"
               onchange="document.getElementById('filename').textContent=this.files[0]?.name||''">
        <div id="filename"></div>
        <button type="submit" style="margin-top:12px;">サーバーにアップロード ↑</button>
    </form>
</div>

<!-- ========== B: サーバー → ローカル ========== -->
<div class="card">
    <h2>📥 サーバー → ローカルにダウンロード</h2>
    <p class="desc">サーバーの会話履歴をローカルPCに取り込みます。</p>

    <div class="step">① 下のボタンで <span>chat_data.json</span> をダウンロード</div>
    <div class="step">② <span>ローカルPC</span> で SAKURA AI を開き、🔄ボタンを押す</div>
    <div class="step">　→ 自動でサーバーから読み込まれます（手動操作不要）</div>

    <a href="?download=1" class="dl-btn btn-blue">サーバーの履歴をダウンロード ↓ （<?= $serverCount ?>件）</a>

    <div class="step" style="margin-top:14px; color:rgba(255,255,255,0.5);">
        または <span>ローカルPC</span> で SAKURA AI を開くだけで自動同期されます。
    </div>
</div>

<!-- ========== C: ローカルからのダウンロード用 ========== -->
<div class="card">
    <h2>💾 ローカルの履歴をダウンロード</h2>
    <p class="desc">このページをローカルPC（192.168.7.120:8080）で開いて使用してください。</p>
    <button onclick="downloadLocal()" class="btn-green">ローカルの履歴をダウンロード</button>
    <div id="dl-status"></div>
</div>

<script>
function downloadLocal(){
    const raw=localStorage.getItem('sakura_convs')||'[]';
    let data;
    try{data=JSON.parse(raw);}catch(e){data=[];}
    const el=document.getElementById('dl-status');
    if(data.length===0){
        el.style.cssText='margin-top:12px;padding:10px;border-radius:6px;background:rgba(255,50,50,0.2);color:#ffb0b0;font-size:13px;';
        el.textContent='❌ ローカルに履歴がありません。http://192.168.7.120:8080/sakura-ai/migrate.phpで開いてください。';
        return;
    }
    const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
    const a=document.createElement('a');
    a.href=URL.createObjectURL(blob);
    a.download='chat_data.json';
    a.click();
    el.style.cssText='margin-top:12px;padding:10px;border-radius:6px;background:rgba(0,200,100,0.2);color:#7fffb0;font-size:13px;';
    el.textContent='✅ '+data.length+'件をダウンロードしました。さくらサーバーのmigrate.phpでアップロードしてください。';
}
</script>
</body>
</html>
