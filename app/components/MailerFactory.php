<?php

namespace App\Components;

use Nette;
use Pushupers\Application\Mailer;


class MailerFactory extends Nette\Object
{
    /** @var ILogger */
    protected $logger;

    /** @var array */
    protected $parameters;


    /**
     * @param ILogger $logger
     */
    public function __construct(ILogger $logger,
                                \Nette\DI\Container $container)
    {
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
    }

}
