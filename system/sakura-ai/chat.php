<?php
/**
 * SAKURA AI Chat - バックエンドAPI
 * Ollama（qwen3:8b）+ Google Custom Search を組み合わせて回答を生成する
 */

require_once __DIR__ . '/config.php';

// ========== 設定 (config.phpから読み込み) ==========
$GOOGLE_API_KEY = GOOGLE_API_KEY;
$GOOGLE_CX = GOOGLE_CX;
$OLLAMA_URL = OLLAMA_URL;
$MODEL_NAME = DEFAULT_MODEL;

// ========== CORS・レスポンスヘッダー ==========
header('Content-Type: application/json; charset=UTF-8');

// ========== POSTリクエストのみ受け付ける ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST リクエストのみ受け付けます']);
    exit;
}

// ========== ユーザーメッセージの取得と検証 ==========
$userMessage = trim($_POST['message'] ?? '');
if ($userMessage === '') {
    echo json_encode(['error' => 'メッセージが空です']);
    exit;
}

// ========== 拡張パラメータの取得 ==========
$reqModel = trim($_POST['model'] ?? '');
if ($reqModel !== '') {
    $MODEL_NAME = $reqModel;
}
$isPrivacy = ($_POST['privacy_mode'] ?? '0') === '1';
$styleLen = trim($_POST['style_len'] ?? '');
$styleTone = trim($_POST['style_tone'] ?? '');
$sysContext = trim($_POST['system_context'] ?? '');

// ========== Google 検索（失敗しても続行する） ==========
$searchContext = '';
if (!$isPrivacy && $GOOGLE_API_KEY !== '' && $GOOGLE_API_KEY !== 'ここにあなたのAPIキーを入力') {
    $searchContext = performGoogleSearch($userMessage, $GOOGLE_API_KEY, $GOOGLE_CX);
}

// ========== Ollama へ送るプロンプトを構築 ==========
$today = date('Y年m月d日');
$prompt = "今日は {$today} です。あなたは SAKURA AI という名前の親切な日本語AIアシスタントです。\n";

if ($sysContext !== '') {
    $prompt .= "【重要なお作法・前提知識】\n{$sysContext}\n\n";
}

$prompt .= "ユーザーの質問に対して、適切に日本語で回答してください。\n";

if ($styleLen === 'short') {
    $prompt .= "※指示：可能な限り短く、要点だけを簡潔にまとめて回答してください。\n";
}
elseif ($styleLen === 'long') {
    $prompt .= "※指示：詳細な背景や理由を含め、できるだけ詳しく長文で回答してください。\n";
}

if ($styleTone === 'frank') {
    $prompt .= "※指示：親しみやすいフランクな口調（タメ口や友人風）で回答してください。敬語は避けてください。\n";
}
else {
    $prompt .= "※指示：丁寧な敬語（です・ます調）で回答してください。\n";
}
$prompt .= "\n";

if ($searchContext !== '') {
    $prompt .= "【参考情報（Google検索結果）】\n";
    $prompt .= $searchContext . "\n\n";
}

$prompt .= "/nothink\n【ユーザーの質問】\n{$userMessage}\n\n【回答】\n";

// ========== Ollama へリクエスト ==========
$ollamaPayload = json_encode([
    'model' => $MODEL_NAME,
    'prompt' => $prompt,
    'stream' => false,
    'think' => false,  // qwen3.5 thinkingモード無効
]);

$ch = curl_init($OLLAMA_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $ollamaPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'ngrok-skip-browser-warning: true',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAKURA-AI/1.0'
    ],
    CURLOPT_TIMEOUT => 180,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);

$raw = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($raw === false || $curlErr) {
    echo json_encode(['error' => 'Ollama 接続エラー (' . $OLLAMA_URL . '): ' . $curlErr]);
    exit;
}

$decoded = json_decode($raw, true);
if ($decoded === null) {
    $preview = substr($raw, 0, 500);
    $errorMsg = 'Ollama からの応答がJSON形式ではありません。';

    // エラー診断ページへの案内を追加
    $errorMsg .= "\n\n詳細な調査にはこちらを実行してください：\n[接続テストを実行] (test_connection.php)";
    // ngrok や接続エラーの特有の文字列が含まれているかチェック
    if (strpos($raw, '<!DOCTYPE html>') !== false) {
        $errorMsg .= "\n\n【原因の可能性】\n";
        if (strpos($OLLAMA_URL, 'ngrok') !== false) {
            $errorMsg .= "・ngrok トンネルが起動していない、または URL が無効です。\n";
            $errorMsg .= "・URLが最新のものか config.php を確認してください。\n";
        }
        else {
            $errorMsg .= "・Ollama が予期せぬ HTML エラーを返しました。サーバーの状態を確認してください。\n";
        }
    }
    // 接続失敗の可能性
    if (strpos($raw, 'Failed to connect to') !== false || strpos($raw, 'Connection refused') !== false) {
        $errorMsg .= "\n\n【原因の可能性】\n";
        $errorMsg .= "・Ollama サービスが起動していません。または、config.php の OLLAMA_URL が間違っています。\n";
        $errorMsg .= "・ファイアウォールやセキュリティソフトが接続をブロックしている可能性があります。\n";
    }

    echo json_encode([
        'error' => $errorMsg,
        'raw_preview' => $preview,
        'version' => APP_VERSION
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!isset($decoded['response']) && !isset($decoded['thinking'])) {
    echo json_encode([
        'error' => 'Ollama 応答内に expected なフィールドが見つかりません。',
        'raw_data' => substr($raw, 0, 200),
        'version' => APP_VERSION
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// responseが空の場合はthinkingフィールドにフォールバック
$rawResponse = $decoded['response'] ?? '';
if ($rawResponse === '' && isset($decoded['thinking'])) {
    // thinkingフィールドから実際の回答部分を抽出
    $thinkingText = $decoded['thinking'];
    // "Thinking Process:" などの思考プロセスヘッダーを除去
    $thinkingText = preg_replace('/^(Thinking Process:|思考プロセス:).*?(\n\n|$)/si', '', $thinkingText);
    $rawResponse = trim($thinkingText);
}
// thinking タグ（<think>...</think>）を除去してクリーンな回答だけ返す
$response = cleanThinkingTags($rawResponse);
// 空のままなら暫定メッセージ
if ($response === '') {
    $response = '（応答を生成中にエラーが発生しました。モデルを再起動してお試しください。）';
}

echo json_encode([
    'response' => $response,
    'version' => APP_VERSION
], JSON_UNESCAPED_UNICODE);

// ========== 関数定義 ==========

/**
 * Google Custom Search で上位 5 件のスニペットを取得する
 */
function performGoogleSearch(string $query, string $apiKey, string $cx): string
{
    $url = 'https://www.googleapis.com/customsearch/v1?'
        . http_build_query([
        'q' => $query,
        'key' => $apiKey,
        'cx' => $cx,
        'num' => 5,
        'lr' => 'lang_ja',
    ]);

    $result = @file_get_contents($url);
    if ($result === false) {
        return '';
    }

    $data = json_decode($result, true);
    if (!isset($data['items'])) {
        return '';
    }

    $text = '';
    foreach ($data['items'] as $item) {
        $title = $item['title'] ?? '';
        $snippet = $item['snippet'] ?? '';
        if ($title || $snippet) {
            $text .= "・{$title}\n  {$snippet}\n\n";
        }
    }

    return trim($text);
}

/**
 * qwen3 の <think>...</think> タグを除去する
 */
function cleanThinkingTags(string $text): string
{
    // <think>...</think> を削除
    $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $text);
    return trim($cleaned ?? $text);
}
