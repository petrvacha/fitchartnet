<?php

namespace Fitchart\Application;

use \Nette\Mail\Message;

/**
 * IMailer
 *
 * @author petrvacha
 */
interface IMailer
{
    /**
     * @param \Fitchart\Application\ILogger $logger
     */
    public function setLogger(\Fitchart\Application\ILogger $logger);

    /**
     * @param \Nette\Mail\Message $mail
     */
    public function send(Message $mail);
}