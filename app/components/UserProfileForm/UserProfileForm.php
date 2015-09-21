<?php

namespace App\Components;

use Nette;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class UserProfileForm extends \Fitchart\Application\Control
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Gender */
    protected $genderModel;

    /** @var \App\Model\Privacy */
    protected $privacyModel;

    /** @var \Nette\Database\Table\ActiveRow */
    protected $userData;

    /** @var int */
    protected $userId;


    /**
     * @param int $userId
     * @param \App\Model\User $userModel
     * @param \App\Model\Gender $genderModel
     * @param \App\Model\Privacy $privacyModel
     */
    public function __construct($userId,
                                \App\Model\User $userModel,
                                \App\Model\Gender $genderModel,
                                \App\Model\Privacy $privacyModel)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->userModel = $userModel;
        $this->genderModel = $genderModel;
        $this->userData = $this->userModel->getUserData($userId);
        $this->privacyModel = $privacyModel;
    }


    /**
     * @return Form
     */
    public function createComponentUserProfileForm()
    {
        $form = new Form;
        $form->addText('firstname', 'Firstname')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50);

        $form->addText('surname', 'Surname')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50);

        $form->addText('email', 'Email')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->addRule(Form::EMAIL, '%label is not a valid email')
            ->addRule(callback($this, 'isEmailAvailable'), 'This email is already taken!');

        $form->addText('username', 'Username')
            ->addRule(Form::FILLED, '%label must be filled')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 50)
            ->addRule(callback($this, 'isUsernameAvailable'), 'This username is already taken!');

        $form->addSelect('gender_id', 'Gender', $this->genderModel->getList())
            ->setPrompt('None');

        $form->addSelect('privacy_id', 'Who can see my stats', $this->privacyModel->getList());

        $form->addTextArea('bio', 'Something about you')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 1000);

        
        $form->addPassword('old_password', 'Old password');
        
        $form->addPassword('password', 'New password')
            ->addRule(Form::MIN_LENGTH, 'Password must be at least %s characters.', 6)
            ->addConditionOn($form['old_password'], Form::FILLED);

        $form->addPassword('confirm_password', 'Confirm new password')
            ->addConditionOn($form['old_password'], Form::FILLED)
                ->addRule(Form::EQUAL, "Passwords don't match", $form['password']);

        $form->addSubmit('submit', 'Save');

        $form->setDefaults($this->userData);
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
        $values['id'] = $this->userId;
        try {
            $this->userModel->updateUserData($values);
            
        } catch (\Fitchart\Application\SecurityException $e) {
            $form->addError($e->getMessage());
        }
    }



    /**
     * @param \Nette\Forms\IControl $userNameCandidate
     * @return bool
     */
    public function isUsernameAvailable($userNameCandidate)
    {
        $user = $this->userModel->findOneBy(['username' => $userNameCandidate->value]);

        if ($user && $user->id === $this->userId) {
            return TRUE;
        } else if ($user) {
            return FALSE;
        } else {
            return TRUE;
        }
    }


    /**
     * @param \Nette\Forms\IControl $emailCandidate
     * @return bool
     */
    public function isEmailAvailable($emailCandidate)
    {
        $user = $this->userModel->findOneBy(['email' => $emailCandidate->value]);

        if ($user && $user->id === $this->userId) {
            return TRUE;
        } else if ($user) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}