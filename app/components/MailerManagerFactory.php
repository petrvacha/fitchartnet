<?php

namespace App\Components;

use Nette;
use Fitchart\Application\Mailer;


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

    /** @var array */
    protected $config;


    /**
     * @param \App\Components\MailerFactory $mailerFactory
     * @param \App\Components\MessageFactory $messageFactory
     * @param Nette\Http\Request $request
     * @param Nette\Application\UI\ITemplateFactory $templateFactory
     * @param array $config
     */
    public function __construct(MailerFactory $mailerFactory, MessageFactory $messageFactory, Nette\Http\Request $request, Nette\Application\UI\ITemplateFactory $templateFactory, array $config)
    {
        $this->mailer = $mailerFactory->init();
        $this->message = $messageFactory->init();
        $this->request = $request;
        $this->templateFactory = $templateFactory;
        $this->config = $config;

    }

    /**
     * @return Mailer
     */
    public function init()
    {
        return new MailerManager($this->mailer, $this->message, $this->request, $this->templateFactory, $this->config);
    }

}
