<?php

namespace Clue;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Tests\Mock\WampComponent;
use Ratchet\Wamp\ServerProtocol as Wamp;

class Messenger implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $raw)
    {
        try{
            $data = $this->parse($raw);
        }
        catch (\RuntimeException $e) {
            return $this->onError($from, $e);
        }

        echo 'Received ' . json_encode($data) . ' from ' . $from->resourceId . PHP_EOL;

        if (!isset($from->user)) {
            $this->authenticate($from, $data);
            return;
        }

        $this->broadcast($data, $from);
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function event($name, $data)
    {
        return $this->broadcast(array(Wamp::MSG_EVENT, $name, $data));
    }

    public function broadcast($data, ConnectionInterface $except = null)
    {
        foreach ($this->clients as $client) {
            /* @var $client ConnectionInterface */
            if ($client === $except) continue;

            $this->send($client, $data);
        }
    }

    protected function send(ConnectionInterface $client, $data)
    {
        $client->send(json_encode($data));
    }

    protected function parse($msg)
    {
        $ret = @json_decode($msg);
        if ($ret === null) {
            throw new \RuntimeException('Invalid message');
        }
        return $ret;
    }

    public function authenticate(ConnectionInterface $client, $data)
    {
        $client->user = 'asd';
    }
}
