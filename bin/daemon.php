<?php

use Ratchet\Server\IoServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$messenger = new Clue\Messenger();

$wsSocket = new Server($loop);
$wsSocket->listen(8081);
$wsServer = new IoServer(new WsServer($messenger), $wsSocket, $loop);

$loop->addPeriodicTimer(5.0, function () use ($messenger) {
    $messenger->event('time', microtime(true));
});

echo 'ws://localhost:8081' . PHP_EOL;
echo 'comet://localhost:8082' . PHP_EOL;
echo 'udp://localhost:8083' . PHP_EOL;

$loop->run();
