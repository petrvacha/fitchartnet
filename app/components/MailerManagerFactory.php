<?php

namespace App\Components;

use Nette;
use Pushupers\Application\Mailer;


class MailerManagerFactory extends Nette\Object
{
    /** @var MailerFactory */
    protected $mailerFactory;

    /** @var MessageFactory */
    protected $messageFactory;

    /** @var  Mailer */
    protected $mailer;

    /** @var  Message */
    protected $message;

    /** @var  Nette\Http\Request */
    protected $request;

    /** @var Nette\Application\UI\ITemplateFactory */
    protected $templateFactory;


    /**
     * @param MailerFactory $mailerFactory
     * @param MessageFactory $messageFactory
     */
    public function __construct(MailerFactory $mailerFactory, MessageFactory $messageFactory, Nette\Http\Request $request, Nette\Application\UI\ITemplateFactory $templateFactory)
    {
        $this->mailer = $mailerFactory->init();
        $this->message = $messageFactory->init();
        $this->request = $request;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @return Mailer
     */
    public function init()
    {
        return new MailerManager($this->mailer, $this->message, $this->request, $this->templateFactory);
    }

}
