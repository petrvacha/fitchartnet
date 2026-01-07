<?php

namespace App\Components;

use Fitchart\Application\Mailer;

class MailerFactory
{
    /** @var ILogger */
    protected $logger;

    /** @var array */
    protected $parameters;


    /**
     * @param \App\Model\LoggerModel $logger
     */
    public function __construct(
        \App\Model\LoggerModel $logger,
        \Nette\DI\Container $container
    ) {
        $this->logger = $logger;
        $this->parameters = $container->getParameters();
    }


    /**
     * @return Mailer
     */
    public function init()
    {
        $mailer = new Mailer();
        $mailer->setLogger($this->logger);
        return $mailer;
    }
}
