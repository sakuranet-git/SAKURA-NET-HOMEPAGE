<?php
declare(strict_types=1);

function shop_read_records(string $path): array
{
    $json = file_get_contents($path);
    if ($json === false || trim($json) === '' || !str_starts_with(ltrim($json), '[')) {
        throw new RuntimeException('保存データを読み込めません。元データは変更していません。');
    }
    $records = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($records) || !array_is_list($records)) {
        throw new RuntimeException('保存データの形式が不正です。元データは変更していません。');
    }
    foreach ($records as $record) {
        if (!is_array($record)) {
            throw new RuntimeException('保存データに不正な行があります。');
        }
    }
    return $records;
}

// A separate lock remains stable when the data file is atomically replaced.
function shop_update_records(string $path, callable $update): void
{
    $lock = fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('保存用ロックを開けません。');
    }
    $temp = null;
    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('保存用ロックを取得できません。');
        }
        $records = shop_read_records($path);
        $next = $update($records);
        if (!is_array($next) || !array_is_list($next)) {
            throw new RuntimeException('保存するデータの形式が不正です。');
        }
        $json = json_encode($next, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
        $temp = $path . '.' . bin2hex(random_bytes(12)) . '.pending';
        $handle = fopen($temp, 'x');
        if ($handle === false) {
            throw new RuntimeException('保存ファイルを作成できません。');
        }
        try {
            if (!chmod($temp, 0600)) {
                throw new RuntimeException('保存ファイルの保護に失敗しました。');
            }
            if (fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
                throw new RuntimeException('保存に失敗しました。元データは変更していません。');
            }
            if (function_exists('fsync') && !fsync($handle)) {
                throw new RuntimeException('保存データの同期に失敗しました。');
            }
        } finally {
            fclose($handle);
        }
        if (!rename($temp, $path)) {
            throw new RuntimeException('保存の切替に失敗しました。元データは変更していません。');
        }
        $temp = null;
    } finally {
        // Preserve a failed write for recovery; do not delete original data.
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function shop_merge_products(array $existing, array $updates, array $deletes): array
{
    $byId = [];
    foreach ($existing as $product) {
        $id = $product['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($byId[$id])) {
            throw new RuntimeException('既存商品のIDが不正です。保存を中止しました。');
        }
        $byId[$id] = $product;
    }
    foreach ($deletes as $id) {
        if (!is_string($id) || !isset($byId[$id])) {
            throw new RuntimeException('削除対象の商品を確認できません。');
        }
        unset($byId[$id]);
    }
    foreach ($updates as $product) {
        $id = $product['id'];
        if (in_array($id, $deletes, true)) {
            throw new RuntimeException('更新と削除が競合しています。');
        }
        $byId[$id] = array_replace($byId[$id] ?? [], $product);
    }
    if ($byId === []) {
        throw new RuntimeException('全商品の削除はできません。');
    }
    return array_values($byId);
}
