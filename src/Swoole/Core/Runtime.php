<?php
namespace Swoole\Core;

class Runtime
{
    static public $runtime;

    const HOOK_RINIT = 1; //request init
    const HOOK_RSHUTDOWN = 2; //request shutdown

    const CORO_REDIS         = 1;
    const CORO_REDIS_HOOK    = 1 << 1;
    const CORO_MYSQL         = 1 << 2;
    const CORO_MYSQL_HOOK    = 1 << 3;
    const CORO_MEMCACHE      = 1 << 4;


    /**
     * 初始化
     * @return Runtime
     */
    static function getInstance()
    {
        if (!self::$runtime)
        {
            self::$runtime = new Runtime;
        }
        return self::$runtime;
    }

    static function enableCoroutine($switch = true, $type = false)
    {
        if (!$type) {
            \Swoole\Runtime::enableCoroutine($switch);
        } else {
            \Swoole\Runtime::enableCoroutine($switch, $type);
        }
    }

    function addRInitHook(callable $callback, $type = false, $prepend = false)
    {
        $this->addHook(self::HOOK_RINIT, $callback, $type, $prepend);
    }

    function addRShutdownHook(callable $callback, $type = false, $prepend = false)
    {
        $this->addHook(self::HOOK_RSHUTDOWN, $callback, $type, $prepend);
    }

    function RInit()
    {
        $this->callHook(self::HOOK_RINIT);
    }

    function RShutdown()
    {
        $this->callHook(self::HOOK_RSHUTDOWN);
    }

    /**
     * 增加钩子函数
     * @param $type
     * @param $func
     * @param $subtype bool
     * @param $prepend bool
     */
    function addHook($type, $func, $subtype = false, $prepend = false)
    {
        if ($subtype)
        {
            if ($prepend)
            {
                array_unshift($this->hooks[$type][$subtype], $func);
            }
            else
            {
                $this->hooks[$type][$subtype][] = $func;
            }
        }
        else
        {
            if ($prepend)
            {
                array_unshift($this->hooks[$type], $func);
            }
            else
            {
                $this->hooks[$type][] = $func;
            }
        }
    }

    /**
     * 执行Hook函数列表
     * @param $type
     * @param $subtype
     */
    function callHook($type,$subtype = false)
    {
        if ($subtype and isset($this->hooks[$type][$subtype]))
        {
            foreach ($this->hooks[$type][$subtype] as $f)
            {
                if (!is_callable($f))
                {
                    trigger_error("Swoole: hook function[$f] is not callable.");
                    continue;
                }
                $f();
            }
        }
        elseif (isset($this->hooks[$type]))
        {
            foreach ($this->hooks[$type] as $f)
            {
                //has subtype
                if (is_array($f) and !is_callable($f))
                {
                    foreach ($f as $subtype => $ff)
                    {
                        if (!is_callable($ff))
                        {
                            trigger_error("Swoole: hook function[$ff] is not callable.");
                            continue;
                        }
                        $ff();
                    }
                } else {
                    if (!is_callable($f))
                    {
                        trigger_error("Swoole: hook function[$f] is not callable.");
                        continue;
                    }
                    $f();
                }
            }
        }
    }


    /**
     * 清理钩子程序
     * @param $type
     */
    function clearHook($type = 0)
    {
        if ($type == 0)
        {
            $this->hooks = array();
        }
        else
        {
            $this->hooks[$type] = array();
        }
    }


}