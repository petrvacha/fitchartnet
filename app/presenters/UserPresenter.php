<?php

namespace App\Presenters;


/**
 * User presenter.
 */
class UserPresenter extends LoginBasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;

    
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

}
