<?php

namespace App\Components;

use App\Model\User;
use Fitchart\Application\SecurityException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class NewPasswordForm extends \Fitchart\Application\Control
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var string */
    protected $token;


    /**
     * @param $token
     * @param \App\Model\User $userModel
     */
    public function __construct($token, User $userModel)
    {
        $this->userModel = $userModel;
        $this->token = $token;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    /**
     * @return Form
     */
    public function createComponentNewPasswordForm()
    {
        $form = new Form();

        $form->addPassword('password', 'New password')
            ->setAttribute('placeholder', 'New password')
            ->setRequired()
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Password must be at least %s characters.', 6);

        $form->addPassword('confirm_password', 'Confirm new password')
            ->setAttribute('placeholder', 'Confirm new password')
            ->setRequired()
            ->addConditionOn($form['password'], Form::FILLED)
            ->addRule(Form::EQUAL, "Passwords don't match", $form['password']);

        $form->addHidden('token');

        $form->addSubmit('submit', 'Change It!');

        $form->setDefaults(['token' => $this->token]);

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        try {
            $this->userModel->updateUserPassword($values);
        } catch (SecurityException $e) {
            $form->addError($e->getMessage());
        }
    }
}
