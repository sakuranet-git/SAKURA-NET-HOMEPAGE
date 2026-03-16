<?php
/**
 * SAKURA AI Chat - ストリーミングバックエンド（安定強化版）
 */

require_once __DIR__ . '/config.php';

// ========== 設定 (config.phpから読み込み) ==========
$TAVILY_API_KEY = defined('TAVILY_API_KEY') ? TAVILY_API_KEY : '';
$GOOGLE_API_KEY = GOOGLE_API_KEY;
$GOOGLE_CX = GOOGLE_CX;
$OLLAMA_URL = OLLAMA_URL;
$MODEL_NAME = $_POST['model'] ?? DEFAULT_MODEL; // 送信されたモデルを優先

const MODE_PROMPTS = [
    'standard' => "あなたは SAKURA AI です。親切で丁寧な日本語アシスタントです。",
    'sales' => "あなたは営業専門AIです。大阪の商売人気質で、効率と利益を重視した提案をします。",
    'code' => "あなたはプログラミング専門AIです。正確なコードと解説を提供します。",
    'creative' => "あなたは作家AIです。豊かな表現力で物語や文章を綴ります。",
];

// ========== SSE & Buffer Config ==========
@set_time_limit(300); // PHPのタイムアウトを延長
if (ob_get_level())
    ob_end_clean();
header('Content-Type: text/event-stream; charset=UTF-8');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);

// ゲートウェイのタイムアウト（502）を防ぐため、即座にヘッダーとプレグレスを送信
echo ": keep-alive begin\n\n";
@ob_flush();
flush();

/**
 * データをJSONエンコードして送信（改行対策）
 */
function sendEvent($event, $data)
{
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush();
    flush();
}

// ========== Logic ==========
$userMessage = trim($_POST['message'] ?? '');
$mode = $_POST['mode'] ?? 'standard';
$useWeather = !empty($_POST['use_weather']);
$useNews = !empty($_POST['use_news']);

if (!$userMessage) {
    sendEvent('error', 'メッセージが空です');
    exit;
}

// 1. Web Search（タイムアウト対策版）
$searchContext = '';
$sources = [];
if ($TAVILY_API_KEY || $GOOGLE_API_KEY) {
    // 検索中イベントを送信
    sendEvent('searching', ['query' => $userMessage]);

    // タイムアウトを非常に短く設定（3秒以内に応答がなければスキップ）
    $searchStartTime = microtime(true);
    $maxSearchTime = 3.0; // 最大3秒

    try {
        if ($TAVILY_API_KEY) {
            [$searchContext, $sources] = performTavilySearchFast($userMessage, $TAVILY_API_KEY, $maxSearchTime);
        }
        else if ($GOOGLE_API_KEY) {
            [$searchContext, $sources] = performGoogleSearchFast($userMessage, $GOOGLE_API_KEY, $GOOGLE_CX, $maxSearchTime);
        }

        // 検索結果があれば送信
        if (!empty($sources)) {
            sendEvent('sources', $sources);
        }
    } catch (Exception $e) {
        error_log("Web search error: " . $e->getMessage());
        $searchContext = '';
        $sources = [];
    }

    // 検索時間のログ
    $searchElapsed = microtime(true) - $searchStartTime;
    error_log("Search completed in {$searchElapsed} seconds");
}

// 2. Weather
$weatherContext = $useWeather ? fetchWeather('Osaka') : '';

// 2.5 News（エラーハンドリング強化）
$newsContext = '';
if ($useNews) {
    try {
        [$newsContext, $newsSources] = fetchAggregatedNews();
        if (!empty($newsSources)) {
            sendEvent('sources', $newsSources);
        }
    } catch (Exception $e) {
        error_log("News fetch exception: " . $e->getMessage());
        $newsContext = '';
    }
}

// (X Twitterの検索ロジックは削除されました)

// 3. Prompt (qwen2.5:14b-instruct 向けコンテキスト最適化)
$sys = MODE_PROMPTS[$mode] ?? MODE_PROMPTS['standard'];
$dateStr = date('Y-m-d');

$prompt = "<|im_start|>system\n{$sys}\n";
$prompt .= "\n【重要指示】あなたは今、リアルタイムでインターネットに接続されており、最新のデータ（ニュース、検索結果、天気）にアクセスできます。\n";
$prompt .= "1. 下記の【ナレッジ：最新情報】に含まれる情報は、**あなたが今この瞬間に知っている確定した事実**です。自信を持って、具体的に回答してください。\n";
$prompt .= "2. **絶対に禁止**: 「私はAIなので最新情報を取得できません」「ニュースサイトを確認してください」などの回答拒否は厳禁です。\n";
$prompt .= "3. ユーザーがニュースを尋ねた場合、検索結果やニュース情報を**必ず具体的に**伝えてください。タイトルだけでなく、内容も要約して説明してください。\n";
$prompt .= "4. 回答は常に**丁寧な日本語**で行ってください。検索結果が英語の場合は、日本語に翻訳・要約してください。\n";
$prompt .= "\n現在の日付: {$dateStr}\n";

if ($weatherContext || $searchContext || $newsContext) {
    $prompt .= "\n━━━━━━━━━━━━━━━━━━━━\n";
    $prompt .= "【あなたが今持っている最新データ】\n";
    $prompt .= "━━━━━━━━━━━━━━━━━━━━\n";
    if ($weatherContext) $prompt .= "\n■ 現在の天気情報:\n{$weatherContext}\n";
    if ($newsContext) $prompt .= "\n■ 最新ニュース:\n{$newsContext}\n";
    if ($searchContext) $prompt .= "\n■ Web検索で取得した情報:\n{$searchContext}\n";
    $prompt .= "\n━━━━━━━━━━━━━━━━━━━━\n";
    $prompt .= "【回答ルール - 厳守】\n";
    $prompt .= "1. 上記のデータは「あなたが今知っている事実」です\n";
    $prompt .= "2. ユーザーの質問には、上記データを使って具体的に答えてください\n";
    $prompt .= "3. 「情報がない」「わからない」「ニュースサイトを見てください」などの回答は絶対禁止\n";
    $prompt .= "4. 特にニュースを聞かれた場合は、見出しだけでなく内容も要約して伝えること\n";
    $prompt .= "━━━━━━━━━━━━━━━━━━━━\n";
}
$prompt .= "<|im_end|>\n<|im_start|>user\n{$userMessage}<|im_end|>\n<|im_start|>assistant\n";

// 回答の先頭に強制的に情報があることを示す
if ($searchContext || $newsContext) {
    $prompt .= "本日の主なニュースをお伝えします:\n\n";
}

// デバッグ: プロンプトをログに出力
file_put_contents(__DIR__ . '/debug_prompt.txt', $prompt);

// 4. Ollama Request
$ollamaData = [
    'model' => $MODEL_NAME,
    'prompt' => $prompt,
    'stream' => true,
    'think' => false,
    'options' => [
        'num_ctx' => 4096,      // コンテキストサイズ制限（メモリ・CPU負荷軽減）
        'num_predict' => 2048,  // 出力トークン数制限
        'temperature' => 0.7,
        'repeat_penalty' => 1.1,
        'num_thread' => 8       // CPU使用スレッド数の適正化（環境に合わせて調整可）
    ]
];

$ch = curl_init($OLLAMA_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($ollamaData),
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'ngrok-skip-browser-warning: true'
    ],
    CURLOPT_TIMEOUT => 300,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        static $jsonBuf = '';
        $jsonBuf .= $data;
        $lines = explode("\n", $jsonBuf);
        $jsonBuf = array_pop($lines);
        foreach ($lines as $line) {
            if (!$line)
                continue;
            $d = json_decode($line, true);
            if (isset($d['response'])) {
                $token = $d['response'];
                
                // 初めてのトークンが来た時に keep-alive メッセージを止めるために何かフラグを立てることも可能
                
                static $thinking = false;
                if (strpos($token, '<think>') !== false) {
                    $thinking = true;
                }
                if (!$thinking) {
                    sendEvent('token', $token);
                }
                if (strpos($token, '</think>') !== false) {
                    $thinking = false;
                }
            }
            if (!empty($d['done']))
                sendEvent('done', true);
        }
        return strlen($data);
    }
]);

$res = curl_exec($ch);
if ($res === false) {
    echo "event: error\ndata: " . json_encode(['msg' => 'Curl error: ' . curl_error($ch)]) . "\n\n";
    file_put_contents(__DIR__ . '/debug_prompt.txt', "\nOllama Curl Error: " . curl_error($ch), FILE_APPEND);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        file_put_contents(__DIR__ . '/debug_prompt.txt', "\nOllama HTTP Error: " . $httpCode, FILE_APPEND);
    }
}
curl_close($ch);

// ========== Utils ==========
function performTavilySearchFast($q, $key, $timeout = 3)
{
    try {
        $url = 'https://api.tavily.com/search';
        $data = [
            'api_key' => $key,
            'query' => $q,
            'search_depth' => 'basic',
            'include_answer' => true,
            'max_results' => 3
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $r = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // タイムアウトまたはエラーの場合は空を返す
        if (!$r || $httpCode != 200) {
            return ['', []];
        }

        $d = json_decode($r, true);
        if (!$d) return ['', []];

        $txt = '';
        $srcs = [];

        if (!empty($d['answer'])) {
            $txt .= $d['answer'] . "\n\n";
        }

        if (!empty($d['results'])) {
            foreach ($d['results'] as $i) {
                $title = $i['title'] ?? '';
                $url = $i['url'] ?? '';
                // コンテンツを150文字に制限してプロンプト肥大化を防止
                $content = mb_substr($i['content'] ?? '', 0, 150) . '...';
                $txt .= "- {$title}\n  {$content}\n";
                $srcs[] = ['title' => $title, 'url' => $url];
            }
        }
        return [$txt, $srcs];
    } catch (Exception $e) {
        return ['', []];
    }
}

function performGoogleSearchFast($q, $key, $cx, $timeout = 3)
{
    try {
        $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query(['q' => $q, 'key' => $key, 'cx' => $cx, 'num' => 3, 'lr' => 'lang_ja']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $r = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$r || $httpCode != 200) {
            return ['', []];
        }

        $d = json_decode($r, true);
        if (!isset($d['items'])) return ['', []];

        $txt = '';
        $srcs = [];
        foreach ($d['items'] as $i) {
            $snippet = mb_substr($i['snippet'] ?? '', 0, 150) . '...';
            $txt .= "・" . ($i['title'] ?? '') . ": " . $snippet . "\n";
            $srcs[] = ['title' => $i['title'] ?? '', 'url' => $i['link'] ?? ''];
        }
        return [$txt, $srcs];
    } catch (Exception $e) {
        return ['', []];
    }
}

function performTavilySearch($q, $key, $includeDomains = null, $maxResults = 3)
{
    try {
        $url = 'https://api.tavily.com/search';
        $data = [
            'api_key' => $key,
            'query' => $q,
            'search_depth' => 'basic',
            'include_answer' => true,
            'max_results' => $maxResults
        ];
        if ($includeDomains) {
            $data['include_domains'] = $includeDomains;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $r = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$r || $httpCode != 200) {
            error_log("Tavily API error: HTTP {$httpCode}, cURL: {$curlError}");
            return ['', []];
        }

        $d = json_decode($r, true);
        if (!$d) return ['', []];

        $txt = '';
        $srcs = [];

        if (!empty($d['answer'])) {
            $txt .= "AI Summary: " . $d['answer'] . "\n\n";
        }

        if (!empty($d['results'])) {
            $txt .= "Sources:\n";
            foreach ($d['results'] as $i) {
                $title = $i['title'] ?? '';
                $url = $i['url'] ?? '';
                $content = mb_substr($i['content'] ?? '', 0, 150) . '...';
                $txt .= "- {$title}\n  {$content}\n";
                $srcs[] = ['title' => $title, 'url' => $url];
            }
        }
        return [$txt, $srcs];
    } catch (Exception $e) {
        error_log("Tavily search exception: " . $e->getMessage());
        return ['', []];
    }
}

function performGoogleSearch($q, $key, $cx)
{
    try {
        $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query(['q' => $q, 'key' => $key, 'cx' => $cx, 'num' => 3, 'lr' => 'lang_ja']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $r = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$r || $httpCode != 200) {
            error_log("Google API error: HTTP {$httpCode}, cURL: {$curlError}");
            return ['', []];
        }

        $d = json_decode($r, true);
        if (!isset($d['items'])) return ['', []];

        $txt = '';
        $srcs = [];
        foreach ($d['items'] as $i) {
            $snippet = mb_substr($i['snippet'] ?? '', 0, 150) . '...';
            $txt .= "・" . ($i['title'] ?? '') . ": " . $snippet . "\n";
            $srcs[] = ['title' => $i['title'] ?? '', 'url' => $i['link'] ?? ''];
        }
        return [$txt, $srcs];
    } catch (Exception $e) {
        error_log("Google search exception: " . $e->getMessage());
        return ['', []];
    }
}

function fetchWeather($city)
{
    $url = "https://wttr.in/{$city}?format=1";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.64.1'); // wttr.in はUser-Agentを見て出力を変えることがあるため
    $r = curl_exec($ch);
    curl_close($ch);
    return $r ? trim($r) : '';
}

function fetchAggregatedNews()
{
    try {
        $txt = "";
        $srcs = [];

        // --- NHK ニュース ---
        $urls = [
            'NHK 主要ニュース' => 'https://www3.nhk.or.jp/rss/news/cat0.xml',
            'Yahoo!ニュース' => 'https://news.yahoo.co.jp/rss/topics/top-picks.xml'
        ];

        foreach ($urls as $sourceName => $url) {
            $ch = curl_init($url);
            if ($ch === false) continue;

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // タイムアウトを2秒に短縮（全体の遅延防止）
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

            $r = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!$r || $httpCode != 200) continue;

            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($r);
            libxml_clear_errors();

            if (!$xml) continue;

            // RSSフォーマットの違いを吸収
            $items = isset($xml->channel->item) ? $xml->channel->item : (isset($xml->item) ? $xml->item : []);
            if (empty($items)) continue;

            $txt .= "【{$sourceName}】\n";
            $count = 0;

            foreach ($items as $item) {
                if ($count >= 4) break; // 各ソース最大4件まで

                $title = trim((string)$item->title);
                $link = trim((string)$item->link);

                if (empty($title)) continue;

                $txt .= "・{$title}\n";
                $srcs[] = ['title' => $title, 'url' => $link];
                $count++;
            }
            $txt .= "\n";
        }

        return [$txt, $srcs];
    } catch (Exception $e) {
        error_log("News exception: " . $e->getMessage());
        return ["", []];
    }
}
