<?php

namespace Clue;

use Datagram\Socket;
use Clue\DatagramPseudoConnection;
use Ratchet\MessageComponentInterface;

class DatagramSocketServer
{
    public function __construct(MessageComponentInterface $app, Socket $socket)
    {
        $socket->on('message', function ($data, $remote) use ($socket, $app) {
            $pseudo = new DatagramPseudoConnection($socket, $remote);
            $pseudo->resourceId    = uniqid();
            $pseudo->remoteAddress = $remote;
            $pseudo->user = 'local';

            $app->onOpen($pseudo);
            $app->onMessage($pseudo, $data);
            $app->onClose($pseudo);
        });
    }
}
