<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * SAKURA AI - 接続テスト用スクリプト
 * このファイルをサーバーにアップロードし、ブラウザでアクセスしてください。
 * 例: https://sakuranet-co.jp/system/sakura-ai/test_connection.php
 */

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "--- SAKURA AI 接続テスト ---\n";
echo "日時: " . date('Y-m-d H:i:s') . "\n";
echo "APP_VERSION: " . (defined('APP_VERSION') ? APP_VERSION : 'undefined (要 config.php 更新)') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'unknown') . "\n";
echo "---------------------------\n\n";

echo "--- 設定の確認 ---\n";
if (!defined('OLLAMA_URL')) {
    die("❌ エラー: config.php に OLLAMA_URL が定義されていません。");
}
echo "TARGET OLLAMA_URL: " . OLLAMA_URL . "\n";
echo "DEFAULT_MODEL: " . (defined('DEFAULT_MODEL') ? DEFAULT_MODEL : 'undefined') . "\n";

// リモート環境なのに localhost になっている場合の警告
if (strpos(OLLAMA_URL, 'localhost') !== false && ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost') {
    echo "⚠️ 【警告】リモートサーバー上で動作していますが、OLLAMA_URL が localhost のままです。\n";
    echo "   自宅PCに繋ぐ場合は config.php で ngrok の URL を指定してください。\n";
}
echo "---------------------------\n\n";

echo "--- PHP環境の確認 ---\n";
echo "cURL: " . (function_exists('curl_init') ? "✅ 使用可能" : "❌ 使用不可") . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? "✅ ON" : "⚠️ OFF (外部URL取得に制限がある可能性)") . "\n";
echo "---------------------------\n\n";

echo "--- 1. GET リクエストテスト (Ollama 起動確認) ---\n";
$ch_get = curl_init(dirname(OLLAMA_URL) . '/'); // URL の末尾を / に
curl_setopt_array($ch_get, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI/1.0'
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp_get = curl_exec($ch_get);
$info_get = curl_getinfo($ch_get);
curl_close($ch_get);

echo "GET ステータスコード: " . $info_get['http_code'] . "\n";
echo "GET 応答内容: " . substr($resp_get, $info_get['header_size'], 100) . "\n\n";

echo "--- 2. POST リクエストテスト (推論テスト) ---\n";
echo "Ollama へテストリクエストを送信中...\n";

$payload = json_encode([
    'model' => DEFAULT_MODEL,
    'prompt' => 'Hi, reply with "OK" if you can hear me.',
    'stream' => false,
    'options' => ['num_predict' => 30],
]);

$ch = curl_init(OLLAMA_URL);
// URL からホスト名を取得
$host = parse_url(OLLAMA_URL, PHP_URL_HOST);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true, // ヘッダーも含める
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Host: ' . $host, // 明示的に Host ヘッダーを指定
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI/1.0'
    ],
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);

$start = microtime(true);
$resp = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
curl_close($ch);
$end = microtime(true);

// ヘッダーとボディの分離
$headerSize = $info['header_size'];
$header = substr($resp, 0, $headerSize);
$raw = substr($resp, $headerSize);

echo "所要時間: " . round($end - $start, 2) . "秒\n";
echo "HTTP ステータスコード: " . $info['http_code'] . "\n";

echo "\n--- 応答ヘッダー ---\n";
echo $header . "\n";

if ($err) {
    echo "❌ CURL エラー: " . $err . "\n";
    if (strpos($err, 'Could not resolve host') !== false) {
        echo "   ⇒ URLが間違っているか、ドメインが有効ではありません。\n";
    }
}

echo "\n--- 応答内容 (最初の2000文字) ---\n";
echo substr($raw ?? '(空の応答)', 0, 5000) . "\n";
echo "---------------------------------------\n";

if ($raw && json_decode($raw)) {
    echo "\n✅ JSONパース成功！接続は正常です。";
}
else {
    echo "\n❌ JSONパース失敗。上記の内容が返されました。\n";

    if ($info['http_code'] == 403) {
        echo "\n💡 【403エラーのヒント】\n";
        echo "1. ngrok の有料機能や特定のセキュリティ設定が有効になっていませんか？\n";
        echo "2. ngrok ダッシュボードの 'Cloud Edge' > 'Endpoints' で制限がかかっていないか確認してください。\n";
        echo "3. 自宅PCの ngrok 画面に '403 Forbidden' のログが出ていれば、 ngrok agent 側での拒否です。\n";
    }
    elseif (strpos($raw, 'Tunnel') !== false && strpos($raw, 'not found') !== false) {
        echo "💡 【解決策】ngrok トンネルが起動していないか、URL が失効しています。\n";
    }
    elseif (strpos($raw, '<!DOCTYPE html>') !== false) {
        echo "💡 【解決策】AI ではなく HTML エラーページが返されています。\n";
        echo "   ngrok の無料枠の警告画面や、サーバーの 404/500 エラーの可能性があります。\n";
    }
}
