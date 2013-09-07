<?php

namespace Clue;

use Ratchet\ConnectionInterface;
use Datagram\Socket;

class DatagramPseudoConnection implements ConnectionInterface
{
    private $socket;
    private $address;

    public function __construct(Socket $socket, $address)
    {
        $this->socket = $socket;
        $this->address = $address;
    }

    public function send($data)
    {
        $this->socket->send($data, $this->address);
    }

    public function close()
    {
        // NOOP
    }
}
