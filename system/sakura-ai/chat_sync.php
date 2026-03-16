<?php
/**
 * SAKURA AI - Chat Sync API
 * 会話履歴をサーバー上のJSONファイルで管理し、
 * ローカル(localStorage)との双方向同期を行う。
 *
 * 対応リクエスト:
 *   GET                              → サーバーの全会話履歴を返す
 *   POST (配列JSON)                  → ローカルとサーバーをマージして返す
 *   POST {"action":"overwrite","data":[...]} → サーバーを上書き保存
 */

// セッションチェック（index.phpと同じ設定）
ini_set('session.gc_maxlifetime', 600);
session_set_cookie_params(['lifetime' => 600]);
session_start();
if (empty($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '認証が必要です']);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONSプリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 保存先ファイル（このPHPと同じディレクトリ）
define('SYNC_FILE', __DIR__ . '/chat_data.json');

// ========== ファイル読み込み関数 ==========
function loadServerData(): array {
    if (!file_exists(SYNC_FILE)) {
        return [];
    }
    $raw = file_get_contents(SYNC_FILE);
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ========== ファイル書き込み関数 ==========
function saveServerData(array $data): bool {
    return file_put_contents(
        SYNC_FILE,
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        LOCK_EX
    ) !== false;
}

// ========== マージ関数 ==========
// id をキーにして、messagesが多い（新しい）方を優先してマージする
function mergeConversations(array $server, array $client): array {
    $merged = [];

    // サーバーデータをidでインデックス化
    foreach ($server as $conv) {
        if (isset($conv['id'])) {
            $merged[$conv['id']] = $conv;
        }
    }

    // クライアントデータをマージ（メッセージ数が多い方を優先）
    foreach ($client as $conv) {
        if (!isset($conv['id'])) continue;
        $id = $conv['id'];

        if (!isset($merged[$id])) {
            // サーバーにない → 追加
            $merged[$id] = $conv;
        } else {
            // 両方にある → messagesが多い方を採用
            $serverMsgCount = count($merged[$id]['messages'] ?? []);
            $clientMsgCount = count($conv['messages'] ?? []);
            if ($clientMsgCount > $serverMsgCount) {
                $merged[$id] = $conv;
            }
        }
    }

    // idの数値順（作成順）でソート
    $result = array_values($merged);
    usort($result, fn($a, $b) => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

    return $result;
}

// ========== GETリクエスト: サーバーデータを返す ==========
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = loadServerData();
    echo json_encode([
        'status' => 'ok',
        'data'   => $data,
        'count'  => count($data),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========== POSTリクエスト ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);

    if ($body === null) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'JSONのパースに失敗しました']);
        exit;
    }

    // --- 上書きモード ---
    // {"action":"overwrite","data":[...]} の形式
    if (isset($body['action']) && $body['action'] === 'overwrite') {
        $data = isset($body['data']) && is_array($body['data']) ? $body['data'] : [];
        if (saveServerData($data)) {
            echo json_encode([
                'status' => 'ok',
                'count'  => count($data),
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'ファイルへの書き込みに失敗しました']);
        }
        exit;
    }

    // --- マージモード ---
    // クライアントから会話配列が直接送られてくる形式
    if (is_array($body)) {
        $serverData = loadServerData();
        $merged     = mergeConversations($serverData, $body);

        if (saveServerData($merged)) {
            echo json_encode([
                'status' => 'ok',
                'merged' => $merged,
                'count'  => count($merged),
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'ファイルへの書き込みに失敗しました']);
        }
        exit;
    }

    // どのモードにも当てはまらない
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '不正なリクエスト形式です']);
    exit;
}

// GET/POST以外
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
