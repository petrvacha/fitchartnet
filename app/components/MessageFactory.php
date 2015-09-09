<?php

namespace App\Components;

use Nette;
use Nette\Mail\Message;
use Nette\DI\Container;
use Fitchart\Application\Mailer;


class MessageFactory extends Nette\Object
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
        $message  = new Message();

        if (isset($this->parameters['config']['emailMessageFrom']) && !empty($this->parameters['config']['emailMessageFrom'])) {
            $message->setFrom($this->parameters['config']['emailMessageFrom'], $this->parameters['config']['emailNameFrom']);
        }

        if (isset($this->parameters['bccLog']) && !empty($this->parameters['bccLog'])) {
            $message->setBcc($this->parameters['bccLog']);
        }

        return $message;
    }

}
