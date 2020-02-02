<?php

namespace App\Components;

use Nette;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class RegistrationForm extends \Fitchart\Application\Control
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
    public function createComponentRegistrationForm()
    {
        $form = new Form;
        $form->addText('username', 'Username')
            ->setRequired('Please enter your username.')
            ->addRule(Form::MIN_LENGTH, '%label must be at least %s characters.', 4)
            ->addRule([$this, 'isUsernameAvailable'], 'This username is already taken!')
            ->setAttribute('placeholder', 'Username');

        $form->addText('email', 'Email')
            ->setRequired('Please enter your email.')
            ->addRule(Form::EMAIL, 'Doesn\'t look like a valid email.')
            ->addRule([$this, 'isEmailAvailable'], 'This email is already taken!')
            ->setAttribute('placeholder', 'Email Address');

        $form->addPassword('password', 'Password')
            ->setRequired('Please enter your password.')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least %s characters.', 6)
            ->setAttribute('placeholder', 'password');


        $form->addSubmit('submit', 'Sign Up');

        $form->onSuccess[] = array($this, 'formSent');

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
            $userData = $this->userModel->add($values);
            $mailerManager = $this->mailerManagerFactory->init();
            $mailerManager->action(MailerManager::REGISTRATION_NEW_USER, $userData);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $form->addError($e->getMessage());
        } catch (\Exception $e) {
            $form->addError($e->getMessage());
        }
    }

    /**
     * @param \Nette\Forms\IControl $userNameCandidate
     * @return bool
     */
    public function isUsernameAvailable($userNameCandidate)
    {
        return $this->userModel->findOneBy(['username' => $userNameCandidate->value]) ? FALSE : TRUE;
    }

    /**
     * @param \Nette\Forms\IControl $emailCandidate
     * @return bool
     */
    public function isEmailAvailable($emailCandidate)
    {
        return $this->userModel->findOneBy(['email' => $emailCandidate->value]) ? FALSE : TRUE;
    }

}