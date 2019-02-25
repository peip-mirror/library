<?php
namespace Swoole\IFace;
use Swoole;

interface Log
{
    function put($msg, $type = Swoole\Log::INFO);
}