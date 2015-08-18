<?php

namespace App\Components;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignFormFactory extends Nette\Object
{
    /** @var User */
    private $user;


    /**
     * @param \Nette\Security\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->addText('username', 'Username:')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Please enter your password.');

        $form->addSubmit('send', 'Sign in');

        $form->onSuccess[] = array($this, 'formSucceeded');
        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function formSucceeded($form, $values)
    {
        $this->user->setExpiration('14 days', FALSE);

        try {
            $this->user->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

}
