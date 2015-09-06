<?php

namespace Fitchart\Application;

use Nette\Mail\SendmailMailer;
use Nette\Mail\Message;

/**
 * Mailer
 *
 * @author petrvacha
 */
class Mailer extends SendmailMailer implements IMailer
{
    /** @var \Fitchart\Application\ILogger */
    protected $logger = NULL;


    /**
     * @param \Fitchart\Application\ILogger $logger
     */
    public function setLogger(\Fitchart\Application\ILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Nette\Mail\Message $mail
     */
    public function send(Message $mail)
    {
        parent::send($mail);
        if ($this->logger) {
            $this->logger->mailLog($mail);
        }
    }
}
