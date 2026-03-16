<?php
/**
 * SAKURA-NET光 申込み管理 - データ保存API
 */

session_start();
header('Content-Type: application/json; charset=UTF-8');

// 認証チェック
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // クッキーチェック
    if (!isset($_COOKIE['SAKURA_AUTH']) || $_COOKIE['SAKURA_AUTH'] !== 'verified') {
        http_response_code(401);
        echo json_encode(['error' => '認証が必要です']);
        exit;
    }
}

$DATA_FILE = __DIR__ . '/sakuranethikari_data.json';

$method = $_SERVER['REQUEST_METHOD'];

// データ読込
if ($method === 'GET') {
    if (file_exists($DATA_FILE)) {
        echo file_get_contents($DATA_FILE);
    } else {
        echo json_encode([]);
    }
    exit;
}

// データ保存
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['error' => '無効なデータです']);
        exit;
    }
    file_put_contents($DATA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => '許可されていないメソッドです']);
