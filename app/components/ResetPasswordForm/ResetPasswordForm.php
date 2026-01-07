<?php

namespace App\Components;

use Fitchart\Application\InvalidArgumentException;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class ResetPasswordForm extends \Fitchart\Application\Control
{
    /** @var MailerManagerFactory */
    public $mailerManagerFactory;


    /** @var \App\Model\User */
    protected $userModel;


    /**
     * @param \App\Model\User $userModel
     * @param MailerManagerFactory $mailerManagerFactory
     */
    public function __construct(\App\Model\User $userModel, MailerManagerFactory $mailerManagerFactory)
    {
        $this->userModel = $userModel;
        $this->mailerManagerFactory = $mailerManagerFactory;
    }


    /**
     * @return Form
     */
    public function createComponentResetPasswordForm()
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('email', 'Email')
            ->setRequired('Please enter your email.')
            ->setAttribute('placeholder', 'Email')
            ->addRule(Form::EMAIL, 'Doesn\'t look like a valid email.');

        $form->addSubmit('submit', 'Reset Password');

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        try {
            $userData = $this->userModel->prepareResetToken($values);
            $mailerManager = $this->mailerManagerFactory->init();
            $mailerManager->action(MailerManager::RESET_PASSWORD, $userData);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $form->addError($e->getMessage());
        } catch (InvalidArgumentException $e) {
            $form->addError($e->getMessage());
        }
    }
}
