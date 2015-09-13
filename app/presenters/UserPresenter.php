<?php

namespace App\Presenters;


/**
 * User presenter
 */
class UserPresenter extends LoginBasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;
    
    
    /** @var \App\Components\UserProfileForm\IUserProfileFormFactory @inject */
    public $userProfileFormFactory;

    /** @var \App\Components\UserPhotoForm\IUserPhotoFormFactory @inject */
    public $userPhotoFormFactory;

    
    /**
     * @param \App\Model\User $userModel
     */
    public function __construct(\App\Model\User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function renderProfile()
    {
        $this->template->user = $this->userModel->getUserData($this->user->id);
        $this->template->title = 'your profile';
    }

    public function renderEdit()
    {
        $this->template->title = 'profile edit';
    }

    /**
     * @return Form
     */
    protected function createComponentUserProfileForm()
    {
        $form = $this->userProfileFormFactory->create($this->user->id);

        $form->onSuccess[] = function () {
            $this->redirect('User:profile');
        };
        return $form;
    }

    /**
     * @return Form
     */
    protected function createComponentUserPhotoForm()
    {
        $form = $this->userPhotoFormFactory->create($this->user->id);

        $form->onSuccess[] = function () {
            $this->redirect('User:profile');
        };
        return $form;
    }

}
