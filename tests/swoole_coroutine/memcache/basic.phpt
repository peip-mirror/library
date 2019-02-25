--TEST--
swoole_coroutine: memcache basic func
--SKIPIF--
<?php require __DIR__ . '/../../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../../include/bootstrap.php';

$mc = new Swoole\Coroutine\Memcache();
$mc->addServer('127.0.0.1', 11211);

go(function () use ($mc) {
    $res = $mc->set('key', ['aa']);
    echo "set res:".var_export($res,1)."\n";

    $res = $mc->get('key');
    echo "get res:".var_export($res,1)."\n";

    $res = $mc->delete('key1');
    echo "delete res:".var_export($res,1)."\n";
    $res = $mc->add('key1', 'data1', 10);
    echo "add res:".var_export($res,1)."\n";
    $res = $mc->set('key2', 'data2', 10);
    echo "set res:".var_export($res,1)."\n";
    $res = $mc->getMulti(['key1', 'key2']);
    echo "getMulti res:".var_export($res,1)."\n";
    $res = $mc->set('counter', 5);
    echo "set res:".var_export($res,1)."\n";
    $res = $mc->increment('counter', 10);
    echo "increment res:".var_export($res,1)."\n";
    $res = $mc->decrement('counter', 10);
    echo "decrement res:".var_export($res,1)."\n";

});
?>
--EXPECTF--
set res:true
get res:array (
  0 => 'aa',
)
delete res:false
add res:true
set res:true
getMulti res:array (
  'key1' => 'data1',
  'key2' => 'data2',
)
set res:true
increment res:15
decrement res:5
