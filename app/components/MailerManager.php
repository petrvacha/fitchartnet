<?php


namespace App\Components;

use Nette;
use Pushupers\Application\IMailer;
use Nette\Mail\Message;
use Pushupers\Application\LogicException;


class MailerManager extends Nette\Object
{
    /** @const REGISTRATION_NEW_USER string */
    const REGISTRATION_NEW_USER = 'registration_new_user';


    /** @var IMailer */
    protected $mailer;


    /**
     * @param IMailer $mailer
     * @param Message $message
     */
    public function __construct(IMailer $mailer, Message $message)
    {
        $this->mailer = $mailer;
        $this->message = $message;
    }

    /**
     * @param string $actionName
     * @param $data
     * @throws NotImplementedException
     */
    public function action($actionName, $data)
    {
        switch($actionName) {
            case self::REGISTRATION_NEW_USER:
                //$this->message->setBody();
                break;

            default:
                throw new NotImplementedException('Error: Action ' . $actionName . ' is not implemented.');
        }

        $this->mailer->send();
    }

}