<?php

namespace Swoole\Coroutine;

use Swoole;

abstract class Pool
{
    /**
     * @var \SplQueue
     */
    protected $pool;
    protected $config;
    protected $type;

    protected $current_entity = 0;
    static $threshold_percent = 1.3;
    static $threshold_num = 10;
    static $threshold_idle_sec = 120;



    static protected $obj = null;

    static function getInstance($config)
    {
        $type = static::getType();
        if (empty($config['object_id']))
        {
            throw new Swoole\Exception\InvalidParam("require object_id");
        }
        if (!self::$obj[$type][$config['object_id']])
        {
            self::$obj[$type][$config['object_id']] = new static($config);
        }
        return self::$obj[$type][$config['object_id']];
    }

    function __construct($config)
    {
        if (empty($config['object_id']))
        {
            throw new Swoole\Exception\InvalidParam("require object_id");
        }
        $this->config = $config;
        $this->pool = new MinHeap();

        $this->type = static::getType().'_'.$config['object_id'];
    }

    function _createObject()
    {
        while (true)
        {
            if ($this->pool->count() > 0)
            {
                $heap_object = $this->pool->extract();
                $object = $heap_object['obj'];
                $time = $heap_object['priority'];
                //超出空闲时间阈值没有活跃不再使用
                if (time() - $time >= self::$threshold_idle_sec)
                {
                    unset($object);
                    continue;
                }
            }
            else
            {
                $object = $this->create();
            }
            $this->current_entity ++;
            Context::put($this->type, $object);
            return $object;
            break;
        }
        return false;
    }

    function _freeObject()
    {
        $cid = Swoole\Coroutine::getuid();
        if ($cid < 0)
        {
            return;
        }
        $object = Context::get($this->type);
        if ($object)
        {
            if ($this->isReuse()) {
                $this->pool->insert(['priority' => time(), 'obj' => $object]);
            }
            Context::delete($this->type);
        }
        $this->current_entity ++;
    }

    protected function _getObject()
    {
        $obj =  Context::get($this->type);
        if ($obj) {
            return $obj;
        } else {
            return $this->_createObject();
        }
    }

    private function isReuse()
    {
        $pool_size = $this->pool->count();
        if ($pool_size == 1) {
            return true;
        }
        if ($this->current_entity > 0 && $pool_size > self::$threshold_num) {
            if ($pool_size / $this->current_entity > self::$threshold_percent) {
                return false;
            }
        }
        return true;
    }

    abstract function create();
    abstract static function getType();
}


class MinHeap extends \SplHeap
{
    /*
     * key => obj
     *
     * */
    public function compare($array1, $array2)
    {
        $p1 = $array1['priority'];
        $p2 = $array2['priority'];
        if ($p1 === $p2) return 0;
        return $p1 > $p2 ? -1 : 1;
    }
}