<?php

namespace Fitchart\Application;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

/**
 * Mailer
 *
 * @author petrvacha
 */
class Mailer extends SendmailMailer implements IMailer
{
    /** @var \Fitchart\Application\ILogger */
    protected $logger = null;


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
    public function send(Message $mail): void
    {
        parent::send($mail);
        if ($this->logger) {
            $this->logger->mailLog($mail);
        }
    }
}
