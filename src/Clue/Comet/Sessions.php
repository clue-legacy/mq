<?php

namespace Clue\Comet;

class Sessions
{
    private $sessions = array();

    /**
     * Timeout for pending comet (long-polling) requests
     *
     * If there's no message to send to the pending request, it will respond
     * with an empty comet response in order to avoid an http timeout.
     *
     * @var float
     */
    private $timeoutComet = 30.0;

    /**
     * Timeout for stale message sessions
     *
     * Messages can be passed to a session even if no consumer actually listens
     * to it. In order to avoid its buffer from growing, timeout the session
     * after 10s if nobody cares for the messages anyway.
     *
     * @var float
     */
    private $timeoutSession = 10.0;

    public function __construct(React\Http\Server $http)
    {
        $http->on('request', array($this, 'handleRequest'));
    }

    public function handleRequest(Request $request, Response $response)
    {
        $session = null;
        $sid = null;
        if (isset($sessions[$sid])) {
            $this->log('resuming existing session ' . $sid);
            $session = $sessions[$sid];
        } else {
            $sid = mt_rand();
            $session = $sessions[$sid] = $this->createMessageSession();
            $this->log('creating new session ' . $sid);

            $this->emit('session', array($session));
        }

        $stream = $this->createCometMessenger($response);

        $session->pipe($stream);

        // TODO: parse request message
        $message = null;
        if ($message !== null) {
            $session->emit('message', array($message));
        }
    }

    /**
     * broadcast message to all sessions
     *
     * @param string $data
     */
    public function write($data)
    {
        foreach ($this->sessions as $session) {
            $session->write($read);
        }
    }

    protected function createMessageSession()
    {
        return new Session();
    }

    protected function createCometMessenger($response)
    {
        return new ResponseStream($response);
    }

    protected function log($msg)
    {
        echo $msg . PHP_EOL;
    }
}
