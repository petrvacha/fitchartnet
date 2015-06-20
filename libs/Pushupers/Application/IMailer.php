<?php

namespace Pushupers\Application;

use App\Model\ILogger;
use \Nette\Mail\Message;

/**
 * IMailer
 *
 * @author petrvacha
 */
interface IMailer
{
    /**
     * @param ILogger $logger
     */
    public function setLogger(ILogger $logger);

    /**
     * @param \Nette\Mail\Message $mail
     */
    public function send(Message $mail);
}