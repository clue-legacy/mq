<?php

namespace Clue;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Tests\Mock\WampComponent;
use Ratchet\Wamp\ServerProtocol as Wamp;

class Messenger implements MessageComponentInterface
{
    protected $clients;
    protected $status;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        // resend current stati
        foreach ($this->clients as $client) {
            if (isset($client->status)) {
                $this->eventOne($conn, 'status', array('uid' => $client->resourceId, 'status' => $client->status));
            }
        }

        $this->setStatus($conn, 'user');
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

//         if (!isset($from->user)) {
//             $this->authenticate($from, $data);
//             return;
//         }

        if ($data[0] === 7 && $data[1] === 'status') {
            $this->setStatus($from, $data[2]);
        }

        $this->broadcast($data, $from);
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";

        $this->setStatus($conn, null);
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

    private function eventOne(ConnectionInterface $client, $name, $data)
    {
        return $this->send($client, array(Wamp::MSG_EVENT, $name, $data));
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

    private function setStatus(ConnectionInterface $client, $status)
    {
        $uid = $client->resourceId;

        $this->event('status', array('uid' => $uid, 'status' => $status));

        if (!$status) {
            unset($client->status);
        } else {
            $client->status = $status;
        }
    }
}
