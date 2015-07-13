<?php


namespace App\Components;

use Latte\Template;
use Nette;
use Pushupers\Application\Control;
use Pushupers\Application\IMailer;
use Nette\Mail\Message;
use Pushupers\Application\LogicException;


class MailerManager extends Nette\Object
{
    /** @const REGISTRATION_NEW_USER string */
    const REGISTRATION_NEW_USER = 'registration_new_user';


    /** @var Nette\Application\UI\ITemplateFactory */
    private $templateFactory;


    /** @var IMailer */
    protected $mailer;

    /** @var Message */
    protected $message;

    /** @var Nette\Http\IRequest */
    protected $httpRequest;


    /**
     * @param IMailer $mailer
     * @param Message $message
     * @param Nette\Http\IRequest $httpRequest
     */
    public function __construct(IMailer $mailer, Message $message, Nette\Http\IRequest $httpRequest, Nette\Application\UI\ITemplateFactory $templateFactory)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        $this->httpRequest = $httpRequest;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param string $actionName
     * @param array $data
     * @param string $lang
     * @throws NotImplementedException
     */
    public function action($actionName, $data, $lang = 'en')
    {
        $domain = $this->httpRequest->getUrl()->host;
        $domainWithScheme = $this->httpRequest->getUrl()->hostUrl;

        switch($actionName) {
            case self::REGISTRATION_NEW_USER:
                $subject = 'Pushupers - Registration';
                $confirmationLink = $domainWithScheme . '/registration/confirm/' . $data['token'];
                $data = array_merge($data, ['domain' => $domain, 'confirmationLink' => $confirmationLink]);
                break;

            default:
                throw new NotImplementedException('Error: Action ' . $actionName . ' is not implemented.');
        }

        $template = $this->templateFactory($actionName, $lang);
        $template->data = $data;
        $this->message->setSubject($subject);
        $this->message->setBody($template);
        $this->mailer->send($this->message);
    }

    /**
     * @param string $action
     * @param string $lang
     * @return Template
     */
    protected function templateFactory($action, $lang)
    {
        return $this->templateFactory->createTemplate()->setFile(APP_DIR . '/presenters/templates/Email/' . $lang . '/' . $action . '.latte');
    }

}