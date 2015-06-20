<?php

namespace App\Components;

use Nette;


class MailerFactory extends Nette\Object
{
    /** @var ILogger */
    private $logger;

    /** @var array */
    private $parameters;

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
    public function create()
    {
        $mailer = new Mailer();

        if ($this->logger instanceof ILogger) {
            $mailer->setLogger($this->logger);
        }
        
        if (isset($this->parameters['fromEmail']) && !empty($this->parameters['fromEmail'])) {
            $mailer->setFrom($this->fromEmail);
        }

        if (isset($this->parameters['bccLog']) && !empty($this->parameters['bccLog'])) {
            $mailer->setBcc($this->bccLog);
        }
    }

}
