<?php
/**
 * SAKURA-NET光 通話明細 - データ保存・同期API
 */

session_start();
header('Content-Type: application/json; charset=UTF-8');

$DATA_FILE = __DIR__ . '/calllog_data.json';

$method = $_SERVER['REQUEST_METHOD'];

// 1. データ取得 (GET) - 認証不要（読み取りのみ）
if ($method === 'GET') {
    if (file_exists($DATA_FILE)) {
        header('Cache-Control: no-cache, must-revalidate');
        echo file_get_contents($DATA_FILE);
    } else {
        echo json_encode([
            'files' => [],
            'deleted' => [],
            'names' => []
        ]);
    }
    exit;
}

// 2. データ保存 (POST) - 認証必要
if ($method === 'POST') {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        if (!isset($_COOKIE['SAKURA_AUTH']) || $_COOKIE['SAKURA_AUTH'] !== 'verified') {
            http_response_code(401);
            echo json_encode(['error' => '認証が必要です']);
            exit;
        }
    }
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['error' => '無効なJSONデータです']);
        exit;
    }

    // データのバリデーション (簡易: filesキーの存在確認程度)
    if (!isset($data['files'])) {
        http_response_code(400);
        echo json_encode(['error' => 'データ形式が不正です']);
        exit;
    }

    // 保存実行
    if (file_put_contents($DATA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'ファイルへの書き込みに失敗しました']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => '許可されていないメソッドです']);
