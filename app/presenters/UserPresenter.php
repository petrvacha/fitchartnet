<?php

namespace App\Presenters;


/**
 * User presenter.
 */
class UserPresenter extends LoginBasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;
    
    
    /** @var \IUserProfileFormFactory @inject */
    public $userProfileFormFactory;

    
    /**
     * @param \App\Model\User $userModel
     */
    public function __construct(\App\Model\User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function renderProfile()
    {
        $this->template->user = $this->userModel->getUserInfo($this->user->id);
    }

    public function renderEdit()
    {
        
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

}
