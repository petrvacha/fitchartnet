<?php

namespace Pushupers\Application;

use \Nette\Mail\Message;

/**
 * IMailer
 *
 * @author petrvacha
 */
interface IMailer
{
    /**
     * @param \Pushupers\Application\ILogger $logger
     */
    public function setLogger(\Pushupers\Application\ILogger $logger);

    /**
     * @param \Nette\Mail\Message $mail
     */
    public function send(Message $mail);
}