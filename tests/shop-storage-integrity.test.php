<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/shop/storage_integrity.php';

function check(bool $ok, string $name): void {
    if (!$ok) throw new RuntimeException('FAIL: ' . $name);
    echo "PASS: $name\n";
}
$dir = dirname(__DIR__) . '/trash/shop-integrity-tests-' . bin2hex(random_bytes(6));
mkdir($dir, 0700, true);
$path = $dir . '/records.json';
foreach (['{broken', '', '{}', '[null]', '["invalid"]'] as $bad) {
    file_put_contents($path, $bad);
    $rejected = false;
    try { shop_update_records($path, static fn(array $rows): array => [['id' => 'new']]); }
    catch (Throwable $e) { $rejected = true; }
    check($rejected && file_get_contents($path) === $bad, 'corruption preserved: ' . strlen($bad));
}
file_put_contents($path, '[{"id":"old","name":"既存履歴"}]');
shop_update_records($path, static function(array $rows): array { $rows[] = ['id'=>'new']; return $rows; });
$rows = shop_read_records($path);
check(count($rows) === 2 && $rows[0]['name'] === '既存履歴', 'append preserves existing Japanese data');
$before = file_get_contents($path);
try { shop_update_records($path, static function(array $rows): array { throw new RuntimeException('test'); }); }
catch (RuntimeException $e) {}
check(file_get_contents($path) === $before, 'failed update leaves original bytes unchanged');
$catalog = [['id'=>'a','amount'=>1,'image'=>'a.webp','category'=>'x','extra'=>'keep'], ['id'=>'b','amount'=>2]];
$merged = shop_merge_products($catalog, [['id'=>'a','amount'=>3]], []);
check(count($merged) === 2 && $merged[0]['extra'] === 'keep' && $merged[0]['image'] === 'a.webp' && $merged[1] === $catalog[1], 'partial submission preserves omitted products and fields');
check(count(shop_merge_products($catalog, [], ['b'])) === 1, 'explicit deletion only');
$failed = false;
try { shop_merge_products($catalog, [], ['a','b']); } catch(RuntimeException $e) { $failed = true; }
check($failed, 'reject deletion of entire catalog');
echo "Fixtures retained under trash (no live data touched).\n";
