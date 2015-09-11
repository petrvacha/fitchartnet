<?php

namespace App\Components;

use Nette;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class UserProfileForm extends \Fitchart\Application\Control
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Privacy */
    protected $privacyModel;

    /** @var Nette\Security\User */
    protected $user;


    /**
     * @param \Nette\Security\User $user
     * @param \App\Model\User $userModel
     * @param \App\Model\Privacy $privacyModel
     */
    public function __construct(\Nette\Security\User $user,
                                \App\Model\User $userModel,
                                \App\Model\Privacy $privacyModel)
    {
        parent::__construct();
        $this->user = $user;
        $this->userModel = $userModel;
        $this->privacyModel = $privacyModel;
    }


    /**
     * @return Form
     */
    public function createComponentUserProfileForm()
    {
        $form = new Form;
        $form->addText('firstname', 'Firstname')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->getControlPrototype()->class = 'form-control';

        $form->addText('surname', 'Surname')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->getControlPrototype()->class = 'form-control';

        $form->addText('email', 'Email')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->addRule(Form::EMAIL, '%label is not a valid email')
            ->addRule(callback($this, 'isEmailAvailable'), 'This email is already taken!')
            ->getControlPrototype()->class = 'form-control';

        $form->addText('username', 'Username')
            ->addRule(Form::FILLED, '%label must be filled')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->addRule(callback($this, 'isUsernameAvailable'), 'This username is already taken!')
            ->getControlPrototype()->class = 'form-control';

        $form->addSelect('privacy_id', 'Who can see my stats', $this->privacyModel->getList())
            ->getControlPrototype()->class = 'form-control';

        
        $form->addText('old_password', 'Old password');
        
        $form->addText('password', 'New password')
            ->addConditionOn($form['old_password'], Form::FILLED);

        $form->addText('password_confirm', 'New password')
            ->addConditionOn($form['old_password'], Form::FILLED)
                ->addRule(Form::EQUAL, "Passwords don't match", $form['password']);

        $form['old_password']->getControlPrototype()->class = 'form-control';
        $form['password']->getControlPrototype()->class = 'form-control';
        $form['password_confirm']->getControlPrototype()->class = 'form-control';

        $form->addSubmit('submit', 'Save')
            ->getControlPrototype()->class = 'btn btn-success';

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
        $values['user_id'] = $this->user->getId();
        
        $values['updated_at'] = new \DateTime;
        $this->activityLogModel->update($values);
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