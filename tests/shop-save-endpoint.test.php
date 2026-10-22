<?php
declare(strict_types=1);
$root = dirname(__DIR__);
$dir = $root . '/trash/shop-endpoint-tests-' . bin2hex(random_bytes(6));
mkdir($dir . '/admin', 0700, true);
mkdir($dir . '/sessions', 0700);
copy($root . '/shop/admin/save.php', $dir . '/admin/save.php');
copy($root . '/shop/admin/products.php', $dir . '/admin/products.php');
copy($root . '/shop/storage_integrity.php', $dir . '/storage_integrity.php');
file_put_contents($dir . '/config.php', '<?php function checkout_products_path(){return __DIR__."/products.json";} function checkout_products(){return json_decode(file_get_contents(checkout_products_path()),true,512,JSON_THROW_ON_ERROR);} function checkout_normalize_stock_status($s){return $s;}');
file_put_contents($dir . '/router.php', '<?php session_save_path(__DIR__."/sessions"); session_start(); $_SESSION["checkout_admin"]=true; session_write_close(); return false;');
file_put_contents($dir . '/run.php', '<?php session_save_path(__DIR__."/sessions"); session_start(); $_SESSION["checkout_admin"]=true; $_SESSION["checkout_csrf"]="fixture"; session_write_close(); $_SERVER["REQUEST_METHOD"]="POST"; $_POST=json_decode(file_get_contents(__DIR__."/post.json"),true); require __DIR__."/admin/save.php";');
$catalog = [];
for ($i=0;$i<202;$i++) $catalog[]=['id'=>'p'.$i,'name'=>'商品'.$i,'description'=>'説明','amount'=>1000,'category'=>'test','image'=>'uploads/a.webp','status'=>'Available','extra'=>'preserve'];
function runSave(array $post): void {
    global $dir;
    file_put_contents($dir.'/post.json', json_encode($post, JSON_THROW_ON_ERROR));
    $process = proc_open([PHP_BINARY,$dir.'/run.php'], [1=>['pipe','w'],2=>['pipe','w']],$pipes);
    $out = stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($process)!==0 || $err!=='') throw new RuntimeException($out.$err);
}
function verify(bool $ok,string $label): void {if(!$ok) throw new RuntimeException('FAIL: '.$label); echo 'PASS: '.$label."\n";}
$path=$dir.'/products.json';
file_put_contents($path,json_encode($catalog));
$row=$catalog[0]; $row['name']='更新済み';
runSave(['csrf'=>'fixture','products_revision'=>hash_file('sha256',$path),'form_complete'=>'1','products_payload'=>json_encode([$row])]);
$saved=json_decode(file_get_contents($path),true);
verify(count($saved)===202 && $saved[0]['name']==='更新済み' && $saved[0]['extra']==='preserve' && $saved[201]===$catalog[201],'single update retains all 202 products/unknown fields');
$before=file_get_contents($path);
runSave(['csrf'=>'fixture','products_revision'=>str_repeat('0',64),'form_complete'=>'1','products_payload'=>json_encode([$row])]);
verify(file_get_contents($path)===$before,'stale revision rejected');
runSave(['csrf'=>'fixture','products_revision'=>hash_file('sha256',$path),'products_payload'=>json_encode([$row])]);
verify(file_get_contents($path)===$before,'missing completion marker rejected');
unset($row['description']);
runSave(['csrf'=>'fixture','products_revision'=>hash_file('sha256',$path),'form_complete'=>'1','products_payload'=>json_encode([$row])]);
verify(file_get_contents($path)===$before,'truncated row rejected');
runSave(['csrf'=>'fixture','products_revision'=>hash_file('sha256',$path),'form_complete'=>'1','products_payload'=>json_encode($catalog)]);
verify(count(json_decode(file_get_contents($path),true))===202,'202-row JSON payload accepted');
runSave(['csrf'=>'fixture','products_revision'=>hash_file('sha256',$path),'form_complete'=>'1','products_payload'=>json_encode([['id'=>'p201','delete'=>'1']])]);
verify(count(json_decode(file_get_contents($path),true))===201,'explicit deletion preserves other 201 products');
echo "Isolated endpoint fixtures only; production untouched.\n";
