<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = __DIR__ . '/data/status_data.json';
$password  = 'sakura2026';

/* ── GETリクエスト：一般公開用の読み取り ── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($dataFile)) {
        echo '[]';
        exit;
    }
    echo file_get_contents($dataFile);
    exit;
}

/* ── POST以外は拒否 ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

/* ── JSONボディのパース ── */
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

/* ── パスワード認証 ── */
if (!isset($input['password']) || $input['password'] !== $password) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: wrong password']);
    exit;
}

/* ── データ読み込み ── */
$data = [];
if (file_exists($dataFile)) {
    $raw  = file_get_contents($dataFile);
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];
}

$action = $input['action'] ?? '';

/* ── 追加 ── */
if ($action === 'add') {
    $item = [
        'id'         => uniqid('s', true),
        'title'      => htmlspecialchars(trim($input['title']   ?? ''), ENT_QUOTES, 'UTF-8'),
        'content'    => htmlspecialchars(trim($input['content'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'service'    => htmlspecialchars(trim($input['service'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'status'     => in_array($input['status'] ?? '', ['failure', 'maintenance', 'normal'])
                            ? $input['status'] : 'normal',
        'date'       => preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'] ?? '')
                            ? $input['date'] : date('Y-m-d'),
        'created_at' => date('Y-m-d H:i:s'),
    ];
    array_unshift($data, $item);
    file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'item' => $item]);

/* ── 編集 ── */
} elseif ($action === 'edit') {
    $id    = $input['id'] ?? '';
    $found = false;
    foreach ($data as &$item) {
        if ($item['id'] === $id) {
            $item['title']      = htmlspecialchars(trim($input['title']   ?? $item['title']),   ENT_QUOTES, 'UTF-8');
            $item['content']    = htmlspecialchars(trim($input['content'] ?? $item['content']), ENT_QUOTES, 'UTF-8');
            $item['service']    = htmlspecialchars(trim($input['service'] ?? $item['service']), ENT_QUOTES, 'UTF-8');
            $item['status']     = in_array($input['status'] ?? '', ['failure', 'maintenance', 'normal'])
                                      ? $input['status'] : $item['status'];
            $item['date']       = preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'] ?? '')
                                      ? $input['date'] : $item['date'];
            $item['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    unset($item);
    if (!$found) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        exit;
    }
    file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);

/* ── 削除 ── */
} elseif ($action === 'delete') {
    $id   = $input['id'] ?? '';
    $data = array_values(array_filter($data, fn($item) => $item['id'] !== $id));
    file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
