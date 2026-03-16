<?php
/**
 * SAKURA Groupware - Google Calendar Sync API
 * 
 * Googleカレンダーとの双方向同期を行います。
 * 
 * 設定手順:
 * 1. Google Cloud Console で OAuth 2.0 クライアントIDを作成
 * 2. リダイレクトURIに https://your-domain.com/gw_calendar_sync.php?action=callback を設定
 * 3. 下記の CLIENT_ID / CLIENT_SECRET を設定
 * 
 * エンドポイント:
 *   ?action=auth       - Google認証URLにリダイレクト
 *   ?action=callback   - OAuth コールバック
 *   ?action=sync       - 同期実行
 *   ?action=status     - 接続状態チェック
 *   ?action=disconnect - 接続解除
 */

session_start();
header('Content-Type: application/json; charset=UTF-8');

// ===== 設定 =====
// TODO: 本番環境ではここを実際のクレデンシャルに置き換えてください
define('GC_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GC_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GC_REDIRECT_URI', 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/gw_calendar_sync.php?action=callback');
define('GC_SCOPES', 'https://www.googleapis.com/auth/calendar');

$DATA_DIR = __DIR__ . '/gw_data';
if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0755, true);

$action = $_GET['action'] ?? '';
$currentUser = $_SESSION['user_id'] ?? 'anonymous';

// トークン管理
function getTokenPath($userId) {
    global $DATA_DIR;
    return $DATA_DIR . '/gcal_token_' . md5($userId) . '.json';
}

function loadToken($userId) {
    $path = getTokenPath($userId);
    if (!file_exists($path)) return null;
    return json_decode(file_get_contents($path), true);
}

function saveToken($userId, $token) {
    file_put_contents(getTokenPath($userId), json_encode($token, JSON_PRETTY_PRINT));
}

function deleteToken($userId) {
    $path = getTokenPath($userId);
    if (file_exists($path)) unlink($path);
}

// アクセストークンの更新
function refreshAccessToken($userId) {
    $token = loadToken($userId);
    if (!$token || !isset($token['refresh_token'])) return null;

    $params = [
        'client_id' => GC_CLIENT_ID,
        'client_secret' => GC_CLIENT_SECRET,
        'refresh_token' => $token['refresh_token'],
        'grant_type' => 'refresh_token'
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $newToken = json_decode($response, true);
    if (isset($newToken['access_token'])) {
        $token['access_token'] = $newToken['access_token'];
        $token['expires_at'] = time() + ($newToken['expires_in'] ?? 3600);
        saveToken($userId, $token);
        return $token;
    }
    return null;
}

// 有効なアクセストークンを取得
function getValidToken($userId) {
    $token = loadToken($userId);
    if (!$token) return null;
    if (time() >= ($token['expires_at'] ?? 0) - 60) {
        $token = refreshAccessToken($userId);
    }
    return $token;
}

// Google Calendar APIコール
function gcalApi($method, $url, $token, $body = null) {
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $token['access_token'],
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    } elseif ($method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

// ==================================================================
// アクション分岐
// ==================================================================
switch ($action) {

    case 'auth':
        // Google OAuth2 認証画面にリダイレクト
        $params = http_build_query([
            'client_id' => GC_CLIENT_ID,
            'redirect_uri' => GC_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => GC_SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $currentUser
        ]);
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;

    case 'callback':
        // OAuthコールバック
        if (isset($_GET['error'])) {
            echo '<script>alert("認証がキャンセルされました"); window.close();</script>';
            exit;
        }

        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? $currentUser;

        $params = [
            'code' => $code,
            'client_id' => GC_CLIENT_ID,
            'client_secret' => GC_CLIENT_SECRET,
            'redirect_uri' => GC_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $token = json_decode($response, true);
        if (isset($token['access_token'])) {
            $token['expires_at'] = time() + ($token['expires_in'] ?? 3600);
            saveToken($state, $token);
            // ポップアップを閉じてメイン画面に通知
            echo '<!DOCTYPE html><html><body><script>
                if (window.opener) {
                    window.opener.postMessage({type:"gcal_connected"}, "*");
                }
                window.close();
            </script><p>Googleカレンダーとの連携が完了しました。このウィンドウを閉じてください。</p></body></html>';
        } else {
            echo '<script>alert("トークンの取得に失敗しました"); window.close();</script>';
        }
        exit;

    case 'status':
        $token = loadToken($currentUser);
        $connected = $token !== null && isset($token['access_token']);
        echo json_encode(['status' => 'ok', 'connected' => $connected]);
        exit;

    case 'disconnect':
        deleteToken($currentUser);
        echo json_encode(['status' => 'ok', 'connected' => false]);
        exit;

    case 'sync':
        // 双方向同期
        $token = getValidToken($currentUser);
        if (!$token) {
            echo json_encode(['status' => 'error', 'message' => 'Googleカレンダーに接続されていません']);
            exit;
        }

        // ローカルスケジュール読み込み
        $schedulePath = $DATA_DIR . '/schedule.json';
        $localEvents = file_exists($schedulePath) ? json_decode(file_get_contents($schedulePath), true) : [];
        if (!is_array($localEvents)) $localEvents = [];

        // Google Calendar からイベント取得 (今月〜3ヶ月先)
        $timeMin = date('c', strtotime('first day of this month'));
        $timeMax = date('c', strtotime('+3 months'));
        $listUrl = 'https://www.googleapis.com/calendar/v3/calendars/primary/events?' . http_build_query([
            'timeMin' => $timeMin,
            'timeMax' => $timeMax,
            'maxResults' => 250,
            'singleEvents' => 'true',
            'orderBy' => 'startTime'
        ]);

        $gcalResult = gcalApi('GET', $listUrl, $token);
        $gcalEvents = $gcalResult['body']['items'] ?? [];

        $synced = 0;
        $imported = 0;

        // 1. ローカル → Google (google_event_idがないものをプッシュ)
        foreach ($localEvents as &$local) {
            if (!empty($local['google_event_id'])) continue;
            if ($local['creator'] !== $currentUser) continue;

            $gcBody = [
                'summary' => $local['title'],
                'description' => $local['description'] ?? '',
                'location' => $local['location'] ?? ''
            ];

            if (!empty($local['all_day'])) {
                $gcBody['start'] = ['date' => $local['start_date']];
                $gcBody['end'] = ['date' => $local['end_date'] ?? $local['start_date']];
            } else {
                $startDT = $local['start_date'] . 'T' . ($local['start_time'] ?? '09:00') . ':00+09:00';
                $endDT = ($local['end_date'] ?? $local['start_date']) . 'T' . ($local['end_time'] ?? '10:00') . ':00+09:00';
                $gcBody['start'] = ['dateTime' => $startDT, 'timeZone' => 'Asia/Tokyo'];
                $gcBody['end'] = ['dateTime' => $endDT, 'timeZone' => 'Asia/Tokyo'];
            }

            $result = gcalApi('POST', 'https://www.googleapis.com/calendar/v3/calendars/primary/events', $token, $gcBody);
            if ($result['code'] === 200 && isset($result['body']['id'])) {
                $local['google_event_id'] = $result['body']['id'];
                $synced++;
            }
        }

        // 2. Google → ローカル (ローカルにないものをインポート)
        $localGcalIds = array_column($localEvents, 'google_event_id');
        foreach ($gcalEvents as $ge) {
            if (in_array($ge['id'], $localGcalIds)) continue;

            $startDate = $ge['start']['date'] ?? substr($ge['start']['dateTime'] ?? '', 0, 10);
            $endDate = $ge['end']['date'] ?? substr($ge['end']['dateTime'] ?? '', 0, 10);
            $startTime = isset($ge['start']['dateTime']) ? substr($ge['start']['dateTime'], 11, 5) : null;
            $endTime = isset($ge['end']['dateTime']) ? substr($ge['end']['dateTime'], 11, 5) : null;

            $localEvents[] = [
                'id' => uniqid('', true),
                'title' => $ge['summary'] ?? '(無題)',
                'description' => $ge['description'] ?? '',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'all_day' => isset($ge['start']['date']),
                'color' => '#4285f4',
                'participants' => [$currentUser],
                'creator' => $currentUser,
                'creator_name' => $_SESSION['user_name'] ?? $currentUser,
                'created_at' => date('Y-m-d H:i:s'),
                'google_event_id' => $ge['id'],
                'location' => $ge['location'] ?? '',
                'source' => 'google'
            ];
            $imported++;
        }

        file_put_contents($schedulePath, json_encode($localEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        echo json_encode([
            'status' => 'ok',
            'synced' => $synced,
            'imported' => $imported,
            'total_events' => count($localEvents)
        ]);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => '不明なアクションです']);
        exit;
}
