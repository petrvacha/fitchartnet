<?php


namespace App\Components;

use Latte\Template;
use Nette;
use Fitchart\Application\Control;
use Fitchart\Application\IMailer;
use Nette\Mail\Message;
use Fitchart\Application\LogicException;


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

    /** @var array */
    protected $config;


    /**
     * @param IMailer $mailer
     * @param Message $message
     * @param Nette\Http\IRequest $httpRequest
     */
    public function __construct(IMailer $mailer, Message $message, Nette\Http\IRequest $httpRequest, Nette\Application\UI\ITemplateFactory $templateFactory, $config)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        $this->httpRequest = $httpRequest;
        $this->templateFactory = $templateFactory;
        $this->config = $config;
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
                $subject = 'Fitchart - Registration';
                $confirmationLink = $domainWithScheme . '/registration/confirm/' . $data['token'];
                $data = array_merge($data, ['domain' => $domain, 'confirmationLink' => $confirmationLink]);
                break;

            default:
                throw new NotImplementedException('Error: Action ' . $actionName . ' is not implemented.');
        }


        $template = $this->templateFactory($actionName, $lang);
        $template->data = $data;
        
        if (!empty($this->config['enabledSendEmail'])) {
            $this->message->addTo($data['email']);
            $this->message->setSubject($subject);
            $this->message->setHTMLBody($template);
            $this->mailer->send($this->message);
        }

        if (!empty($this->config['enabledSaveEmail'])) {
            $time = date("Y-m-d H:i:s");

            $template = strip_tags($template);
            $message = "Odesílám email [{$time}]\n"
                     . "From: {$this->message->getHeader('From')}"
                     . "To: {$this->message->getHeader('To')}"
                     . "Subject: {$subject}"
                     . "Body:\n{$template}"
                     . "\n\n Konec -----------------------------------\n\n\n\n";

            \Tracy\Debugger::log($message);
        }
    }

    /**
     * @param string $action
     * @param string $lang
     * @return Template
     */
    protected function templateFactory($action, $lang)
    {
        return $this->templateFactory->createTemplate()->setFile(APP_DIR . '/templates/Email/' . $lang . '/' . $action . '.latte');
    }

}