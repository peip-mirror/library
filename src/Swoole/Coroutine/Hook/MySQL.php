<?php

namespace Swoole\Coroutine\Hook;

use Swoole\Coroutine\Pool;
use Swoole\Coroutine\Context;
use Swoole\Database\MySQLi as CoMysql;


class MySQL extends Pool
{
    protected $type = 'mysql';

    static function init()
    {

    }

    function __construct($config)
    {
        parent::__construct($config);
        \Swoole\Core\Runtime::getInstance()->addRInitHook([$this, '_createObject'], __CLASS__);
        \Swoole\Core\Runtime::getInstance()->addRShutdownHook([$this, '_freeObject'], __CLASS__);
    }

    function create()
    {
        $db = new CoMySQL($this->config);
        if ($db->connect() === false)
        {
            return false;
        }
        else
        {
            return $db;
        }
    }

    function query($sql)
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        $result = false;
        for ($i = 0; $i < 2; $i++)
        {
            $result = $db->query($sql);
            if ($result === false)
            {
                $db->close();
                Context::delete($this->type);
                $db = $this->_createObject();
                continue;
            }
            break;
        }

        return $result;
    }

    /**
     * 调用$driver的自带方法
     * @param $method
     * @param array $args
     * @return mixed
     */
    function __call($method, $args = array())
    {
        $obj = $this->_getObject();
        if (!$obj)
        {
            return false;
        }
        return $obj->{$method}(...$args);
    }
}