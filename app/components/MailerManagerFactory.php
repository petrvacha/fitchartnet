<?php

namespace App\Components;

use Nette;
use Pushupers\Application\Mailer;


class MailerManagerFactory extends Nette\Object
{
    /** @var MailerFactory @inject */
    protected $mailerFactory;

    /** @var MessageFactory @inject */
    protected $messageFactory;

    /** @var  Mailer */
    protected $mailer;

    /** @var  Message */
    protected $message;


    public function __construct()
    {
        $this->mailer = $this->mailerFactory->init();
        $this->message = $this->messageFactory->init();
    }

    /**
     * @return Mailer
     */
    public function init()
    {
        return new MailerManager($this->mailer, $this->message);
    }

}
