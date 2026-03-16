<?php
/**
 * SAKURA AI - 超・精密接続状況診断 (v2)
 */

require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=UTF-8');
echo "<h1>🌸 SAKURA AI - 精密接続診断</h1>";

$dir = __DIR__;
$file = $dir . '/sakura_history.json';
$ollama_base = str_replace('/api/generate', '', OLLAMA_URL);

// 1. 権限
echo "<h2>1. パーミッション確認</h2>";
echo is_writable($dir) ? "✅ フォルダ: OK<br>" : "❌ フォルダ: 権限なし (755/777推奨)<br>";
if (file_exists($file)) {
    echo is_writable($file) ? "✅ 履歴JSON: OK<br>" : "❌ 履歴JSON: 権限なし (666/777推奨)<br>";
}

// 2. 通信テスト (詳細)
function test_url($url, $method = 'GET', $post_data = null)
{
    $ch = curl_init($url);
    $headers = [
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI-Diagnostic/1.0'
    ];
    if ($post_data)
        $headers[] = 'Content-Type: application/json';

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false, // さくらからngrokへの通信用
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HEADER => true, // ヘッダーも含める
    ]);
    if ($post_data)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return ['res' => $response, 'info' => $info, 'err' => $err];
}

echo "<h2>2. 通信テスト</h2>";

// A. ngrok の死活確認 (Root)
echo "<h3>(A) ngrok 接続確認 (Root)</h3>";
$resA = test_url($ollama_base);
if ($resA['err']) {
    echo "❌ 接続失敗: " . $resA['err'] . "<br>";
}
else {
    echo "✅ 通信成功 (HTTP " . $resA['info']['http_code'] . ")<br>";
    if (strpos($resA['res'], 'Ollama is running') !== false) {
        echo "✨ <strong>判定: Ollama に正常に繋がっています！</strong><br>";
    }
    else {
        echo "⚠️ 判定: 繋がりましたが、Ollama ではない何かが応答しています。<br>";
        echo "<details><summary>応答ヘッダー・本文を表示</summary><pre>" . htmlspecialchars(substr($resA['res'], 0, 1000)) . "</pre></details>";
    }
}

// B. Ollama API 確認 (POST)
echo "<h3>(B) AI応答テスト (POST)</h3>";
$payload = json_encode(['model' => DEFAULT_MODEL, 'prompt' => 'hi', 'stream' => false]);
$resB = test_url(OLLAMA_URL, 'POST', $payload);

if ($resB['err']) {
    echo "❌ 失敗: " . $resB['err'] . "<br>";
}
else {
    echo "✅ 応答あり (HTTP " . $resB['info']['http_code'] . ")<br>";
    $body = substr($resB['res'], $resB['info']['header_size']);
    echo "<pre>受信データ: " . htmlspecialchars($body) . "</pre>";

    if (empty($body)) {
        echo "❌ <strong>エラー: 応答が空弾です。ngrokの設定かOllamaの起動状態を確認してください。</strong><br>";
    }
}

echo "<hr><p>この診断が終わったらファイルを削除してください。🌸</p>";
