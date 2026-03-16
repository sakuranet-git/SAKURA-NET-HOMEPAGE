<?php
/**
 * SAKURA AI - Ollama モデル一覧取得API
 * Ollamaの /api/tags エンドポイントからインストール済みモデルを取得します。
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

// OLLAMA_URL (例: http://localhost:11434/api/generate) からベースURLを抽出
$parsedUrl = parse_url(OLLAMA_URL);
$baseUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? 'localhost') . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '');
$tagsUrl = $baseUrl . '/api/tags';

// Ollama API /api/tags へリクエスト（インストール済みモデル）
$ch = curl_init($tagsUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI/1.0'
    ],
    CURLOPT_TIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);
$rawTags = curl_exec($ch);
curl_close($ch);

// Ollama API /api/ps へリクエスト（現在稼働中のモデル）
$psUrl = $baseUrl . '/api/ps';
$ch2 = curl_init($psUrl);
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI/1.0'
    ],
    CURLOPT_TIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);
$rawPs = curl_exec($ch2);
curl_close($ch2);

if ($rawTags === false) {
    echo json_encode(['error' => 'Ollama 接続エラー: /api/tags に接続できません']);
    exit;
}

$decodedTags = json_decode($rawTags, true);
$decodedPs = $rawPs !== false ? json_decode($rawPs, true) : null;

if ($decodedTags === null || !isset($decodedTags['models'])) {
    echo json_encode(['error' => 'モデル一覧の取得に失敗しました。']);
    exit;
}

// 稼働中のモデル名を取得
$runningModels = [];
if ($decodedPs !== null && isset($decodedPs['models'])) {
    foreach ($decodedPs['models'] as $rm) {
        if (isset($rm['name'])) {
            $runningModels[] = $rm['name'];
        }
    }
}

// モデルリストの構築
$models = [];
foreach ($decodedTags['models'] as $m) {
    if (isset($m['name'])) {
        $models[] = [
            'name' => $m['name'],
            'is_running' => in_array($m['name'], $runningModels)
        ];
    }
}

echo json_encode(['models' => $models]);
