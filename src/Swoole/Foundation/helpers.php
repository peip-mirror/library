<?php
if (!function_exists('_string'))
{
    /**
     * @param  string $str
     * @return Swoole\Core\StringObject
     */
    function _string($str)
    {
        return new Swoole\Core\StringObject($str);
    }
}

if (!function_exists('_array'))
{
    /**
     * @param  array $array
     * @return Swoole\Core\ArrayObject
     */
    function _array($array)
    {
        return new Swoole\Core\ArrayObject($array);
    }
}
