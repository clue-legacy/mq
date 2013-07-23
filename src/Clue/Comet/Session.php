<?php

namespace Clue\Comet;

use React\Stream\Stream;

class Session extends Stream
{
    const TIMEOUT_KEEPALIVE = 10.0;

    private $timeout = null;

    public function __construct()
    {
        $this->armTimeout();
    }

    public function onPipe()
    {
        // piping to somebody, so buffer will decrease, remove timeout
        $this->clearTimeout();
    }

    public function onUnPipe()
    {
        // piping to nobody, so buffer keeps filling...
        // TODO:
        if (true) {
            $this->armTimeout();
        }
    }

    private function clearTimeout()
    {
        if ($this->timeout !== null) {
            $this->loop->removeTimer($this->timeout);
            $this->timeout = null;
        }
    }

    private function armTimeout()
    {
        $this->clearTimeout();
        $this->timeout = $this->loop->addTimer(self::TIMEOUT_KEEPALIVE, array($this, 'timeout'));
    }

    public function timeout()
    {
        $this->close();
    }

    public function close()
    {
        $this->clearTimeout();
    }
}
