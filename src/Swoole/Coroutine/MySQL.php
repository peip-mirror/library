<?php

namespace Swoole\Coroutine\Component;

use Swoole\Coroutine\Context;
use Swoole\IDatabase;
use Swoole\IDbRecord;

class MySQL extends Pool implements IDatabase
{
    protected $type = 'mysql';

    function __construct($config)
    {
        parent::__construct($config);
        \Swoole\Core\Runtime::getInstance()->addRInitHook([$this, '_createObject'], __CLASS__);
        \Swoole\Core\Runtime::getInstance()->addRShutdownHook([$this, '_freeObject'], __CLASS__);
    }

    function create()
    {
        $db = new Swoole\Coroutine\MySQL();
        if ($db->connect($this->config) === false)
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

        return new MySQLRecordSet($result);
    }

    function quote($val)
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }
        if (empty($val))
        {
            return $val;
        }
        return $db->escape($val);
    }

    function lastInsertId()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        return $db->insert_id;
    }

    function getAffectedRows()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return false;
        }

        return $db->affected_rows;
    }

    function errno()
    {
        /**
         * @var $db CoMySQL
         */
        $db = $this->_getObject();
        if (!$db)
        {
            return -1;
        }

        return $db->errno;
    }

    function close()
    {

    }

    function connect()
    {

    }
}

class MySQLRecordSet implements IDbRecord
{
    public $result;

    function __construct($result)
    {
        $this->result = $result;
    }

    function fetch()
    {
        return isset($this->result[0]) ? $this->result[0] : null;
    }

    function fetchall()
    {
        return $this->result;
    }

    function __get($key)
    {
        return $this->result->$key;
    }
}
