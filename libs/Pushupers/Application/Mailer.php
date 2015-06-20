<?php

namespace Pushupers\Application;

use Nette\Mail\SendmailMailer;
use Nette\Mail\Message;
use App\Model\ILogger;

/**
 * Mailer
 *
 * @author petrvacha
 */
class Mailer extends SendmailMailer implements IMailer
{
    protected $logger = NULL;

    /**
     * @param ILogger $logger
     */
    public function setLogger(ILogger $logger)
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
