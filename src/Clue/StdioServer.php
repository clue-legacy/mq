<?php

namespace Clue;

use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

class StdioServer
{
    public function __construct(MessageComponentInterface $app, LoopInterface $loop)
    {
        $loop->addReadStream(STDIN, function() use ($app) {
            $parts = explode(' ', trim(fgets(STDIN, 8192)), 2);
            if (count($parts) == 2) {
                $data = (json_decode($parts[1]));
                if ($data === null) {
                    $data = json_decode(json_encode($parts[1]));
                }
                echo 'channel "'. $parts[0] . '" send "' . json_encode($data) . '"' . PHP_EOL;
                $app->event($parts[0], $data);
            }
        });
    }
}
