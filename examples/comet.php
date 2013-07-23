<?php

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$sessions = array();

$http = new React\Http\Server($socket);

$sessions = new Clue\Comet\Sessions($http);

$loop->addReadStream(STDIN, function() use ($loop, $socket, $sessions) {
    if (feof(STDIN)) {
        $loop->removeReadStream(STDIN);
        $socket->shutdown();
        return;
    }

    $read = rtrim(fgets(STDIN), "\r\n");

    $sessions->write($read);
});

$socket->listen(1337);
$loop->run();
