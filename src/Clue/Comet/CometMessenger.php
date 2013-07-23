<?php

namespace Clue\Comet;

class ComentMessenger extends React\Stream\Stream
{
    const TIMEOUT_WAIT = 30;

    public function __construct(Response $response, LoopInterface $loop)
    {
        $this->response = $response;

        // no message to send as reply => send empty message in order to avoid HTTP timeout
        $loop->addTimer(self::TIMEOUT_WAIT, array($this, 'timeout'));
    }

    public function timeout()
    {
        $this->end();
    }

    public function write($data)
    {
        $this->response->writeHead(200, array(
                'Content-Type'   => 'text/plain',
                'Content-Length' => strlen($data)
        ));
        $this->response->end($data);

        $this->end();
    }
}
