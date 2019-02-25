<?php
require dirname(__DIR__)."/include/autoload.php";

use Swoole\Core\Runtime;
class Server
{
    public $server;
    public $redisPool = [];
    public $redis;

    public function run()
    {
        $this->server = new Swoole\Http\Server("0.0.0.0", 9501);
        $this->server->set([
            'worker_num' => 1,
        ]);
        $this->server->on('WorkerStart', [$this, 'WorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->start();
    }

    public function WorkerStart($serv, $worker_id)
    {
        Runtime::enableCoroutine();
        echo "worker $worker_id start \n";
    }

    public function onRequest($request, $response)
    {
        Runtime::getInstance()->RInit();
        $redis = Swoole\Coroutine\Hook\Redis::getInstance(array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0.5,
            'object_id' => 'master'
        ));
        $redis_data = $redis->get("key");

        $mysql = Swoole\Coroutine\Hook\MySQL::getInstance(array(
            'type'    => Swoole\Database::TYPE_MYSQLi,
            'host'    => "127.0.0.1",
            'port'    => 3306,
            'dbms'    => 'mysql',
            'user'    => "root",
            'passwd'  => "root",
            'name'    => "test",
            'charset' => "utf8",
            'setname' => true,
            'object_id' => 'master'
        ));
        $mysql_data = $mysql->query("select * from test limit 1")->fetch();
        $ret = [
            'redis' => $redis_data,
            'db' => $mysql_data,
        ];
        $response->end(json_encode($ret,JSON_UNESCAPED_UNICODE));
        Runtime::getInstance()->RShutdown();
    }
}

$server = new Server();
$server->run();
