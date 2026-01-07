<?php


namespace App\Components;

use Fitchart\Application\IMailer;
use Fitchart\Application\NotImplementedException;
use Latte\Template;
use Nette;
use Nette\Mail\Message;

class MailerManager
{
    /** @const REGISTRATION_NEW_USER string */
    public const REGISTRATION_NEW_USER = 'registration_new_user';

    /** @const RESET_PASSWORD string */
    public const RESET_PASSWORD = 'reset_password';


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
     * @param \Fitchart\Application\IMailer $mailer
     * @param \Nette\Mail\Message $message
     * @param Nette\Http\IRequest $httpRequest
     * @param Nette\Application\UI\ITemplateFactory $templateFactory
     * @param array $config
     */
    public function __construct(
        IMailer $mailer,
        Message $message,
        Nette\Http\IRequest $httpRequest,
        Nette\Application\UI\ITemplateFactory $templateFactory,
        $config
    ) {
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

        switch ($actionName) {
            case self::REGISTRATION_NEW_USER:
                $subject = 'Fitchart.net - Registration';
                $confirmationLink = $domainWithScheme . '/registration/confirm/' . $data['token'];
                $data = array_merge($data, ['domain' => $domain, 'confirmationLink' => $confirmationLink]);
                break;
            case self::RESET_PASSWORD:
                $subject = 'Fitchart.net - Reset password';
                $resetLink = $domainWithScheme . '/new-password/' . $data['token'];
                $data = array_merge($data, ['domain' => $domain, 'resetLink' => $resetLink]);
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

            $fromArray = $this->message->getHeader('From');
            reset($fromArray);
            $from = key($fromArray);

            $message = "Odesílám email [{$time}]\n "
                     . "From: {$from}\n "
                     . "To: {$data['email']}\n "
                     . "Subject: {$subject}\n "
                     . "Body:\n {$template}\n\n "
                     . "Konec -----------------------------------\n\n\n\n";

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
