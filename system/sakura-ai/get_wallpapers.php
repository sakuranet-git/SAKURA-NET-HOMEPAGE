<?php
/**
 * SAKURA AI Chat v0.20 - 壁紙リスト取得API
 * wallpapers/ フォルダ内の画像ファイル一覧を JSON で返す
 */
header('Content-Type: application/json; charset=UTF-8');

$dir = __DIR__ . '/wallpapers';
$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$images = [];

if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $images[] = 'wallpapers/' . $file;
        }
    }
}

echo json_encode(['status' => 'ok', 'images' => $images], JSON_UNESCAPED_UNICODE);
