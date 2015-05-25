<?php

namespace App\Components;

use Nette;
use Nette\Application\UI\Form;


class RegistrationFormFactory extends Nette\Object
{
    /** @var App\Model\User */
    protected $userModel;

    /**
     * @param App\Model\User $userModel
     */
    public function __construct(\App\Model\User $userModel)
    {
        $this->userModel = $userModel;
    }


    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->addText('username', 'Username')
            ->setRequired('Please enter your username.')
            ->addRule(Form::MIN_LENGTH, '%label must be at least %s characters.', 4)
            ->addRule(callback($this, 'isUsernameAvailable'), 'This username is already taken!');

        $form->addText('email', 'Email')
            ->setRequired('Please enter your email.')
            ->addRule(Form::EMAIL, 'Doesn\'t look like a valid email.')
            ->addRule(callback($this, 'isEmailAvailable'), 'This email is already taken!');

        $form->addPassword('password', 'Password')
            ->setRequired('Please enter your password.')
            ->addRule(Form::MIN_LENGTH, 'Username must be at least %s characters.', 6);


        $form->addSubmit('send', 'Sign Up');

        $form->onSuccess[] = array($this, 'registrationFormSent');
        return $form;
    }

    /**
     * @param Form $form
     * @param type $values
     */
    public function registrationFormSent($form, $values)
    {
        try {
            $this->userModel->add($values);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $form->addError($e->getMessage());
        }
    }


    /**
     * @param Nette\Forms\IControl $userNameCandidate
     * @return bool
     */
    public function isUsernameAvailable($userNameCandidate)
    {
        return $this->userModel->findOneBy(['username' => $userNameCandidate->value]) ? FALSE : TRUE;
    }


    /**
     * @param Nette\Forms\IControl $emailCandidate
     * @return bool
     */
    public function isEmailAvailable($emailCandidate)
    {
        return $this->userModel->findOneBy(['email' => $emailCandidate->value]) ? FALSE : TRUE;
    }

}
