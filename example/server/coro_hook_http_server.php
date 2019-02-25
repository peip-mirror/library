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

        // $this->server->on('Connect', [$this, 'onConnect']);
        $this->server->on('WorkerStart', [$this, 'WorkerStart']);
        $this->server->on('Request', [$this, 'onRequest']);
//         $this->server->on('Close', [$this, 'onClose']);
        $this->server->start();
    }

    public function WorkerStart($serv, $worker_id)
    {
        Runtime::enableCoroutine();
        $this->redis = new Swoole\Coroutine\Hook\Redis(array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0.5,
            'object_id' => 'master'
        ));

        $this->db = new Swoole\Coroutine\Hook\MySQL(array(
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
        echo "worker $worker_id start \n";
    }

    public function onRequest($request, $response)
    {
        Runtime::getInstance()->RInit();
        $res = $this->redis->set("key", "hello swoole");
        $data = $this->redis->get("key");
        $db = $this->db->query("select * from test limit 1")->fetch();
        $ret = [
            'redis' => $res,
            'db' => $db,
        ];
        $response->end(json_encode($ret,JSON_UNESCAPED_UNICODE));
        Runtime::getInstance()->RShutdown();
    }
}

$server = new Server();
$server->run();
