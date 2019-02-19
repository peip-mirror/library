<?php
namespace Co;

class WaitGroup
{
    private $count = 0;
    private $chan;

    /**
     * waitgroup constructor.
     * @desc 初始化一个channel
     */
    public function __construct()
    {
        $this->chan = new \chan;
    }

    public function add()
    {
        $this->count++;
    }

    public function done()
    {
        $this->chan->push(true);
    }

    public function wait()
    {
        while($this->count--)
        {
            $this->chan->pop();
        }
    }
}
