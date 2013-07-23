<?php

namespace Clue\Comet;

/**
 *
 * @event message($data, $thisMessageInterface)
 * @event close()
 */
interface MessageInterface extends EventEmitterInterface
{
    public function send($data);
}
