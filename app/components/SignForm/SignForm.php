<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;

/**
 * SignForm component
 */
class SignForm extends \Fitchart\Application\Control
{
    /** @var \Nette\Security\User */
    protected $user;

    /** @var \App\Model\User */
    protected $userModel;

    
    /**
     * @param \Nette\Security\User $user
     * @param \App\Model\User $userModel
     */
    public function __construct(\Nette\Security\User $user,
                                \App\Model\User $userModel)
    {
        $this->user = $user;
        $this->userModel = $userModel;
    }

    /**
     * @return Form
     */
    public function createComponentSignForm()
    {
        $form = new Form;
        $form->addText('username', 'Username:')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Please enter your password.');

        $form->addSubmit('send', 'Sign in');

        $form->onSuccess[] = array($this, 'formSent');
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
        $this->user->setExpiration('14 days', FALSE);

        try {
            $this->user->login($values->username, $values->password);
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}