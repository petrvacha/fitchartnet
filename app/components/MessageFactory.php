<?php

namespace App\Components;

use Fitchart\Application\Mailer;
use Nette\DI\Container;
use Nette\Mail\Message;

class MessageFactory
{
    /** @var  Mailer */
    protected $mailer;

    /** @var  Message */
    protected $message;

    /** @var  array */
    protected $parameters;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->parameters = $container->getParameters();
    }

    /**
     * @return Message
     */
    public function init()
    {
        $message = new Message();

        if (isset($this->parameters['config']['emailMessageFrom']) && !empty($this->parameters['config']['emailMessageFrom'])) {
            $message->setFrom($this->parameters['config']['emailMessageFrom'], $this->parameters['config']['emailNameFrom']);
        }

        if (isset($this->parameters['bccLog']) && !empty($this->parameters['bccLog'])) {
            $message->setBcc($this->parameters['bccLog']);
        }

        return $message;
    }
}
