<?php

namespace Swoole\Coroutine\Hook;

use Swoole\Coroutine\Pool;
use Swoole\Component\Redis as CoRedis;

class Redis extends Pool
{
    protected $type = 'redis';

    function __construct($config)
    {
        parent::__construct($config);
        \Swoole\Core\Runtime::getInstance()->addRInitHook([$this, '_createObject'], __CLASS__);
        \Swoole\Core\Runtime::getInstance()->addRShutdownHook([$this, '_freeObject'], __CLASS__);
    }

    function create()
    {
        return new CoRedis($this->config);
    }

    /**
     * 调用$driver的自带方法
     * @param $method
     * @param array $args
     * @return mixed
     */
    function __call($method, $args = array())
    {
        $redis = $this->_getObject();
        if (!$redis)
        {
            return false;
        }
        return $redis->{$method}(...$args);
    }
}
