<?php

namespace App\Presenters;


/**
 * User presenter
 */
class UserPresenter extends LoginBasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \Nette\Http\Session */
    protected $session;


    /** @var \App\Components\UserProfileForm\IUserProfileFormFactory @inject */
    public $userProfileFormFactory;

    /** @var \App\Components\UserPhotoForm\IUserPhotoFormFactory @inject */
    public $userPhotoFormFactory;


    /**
     * @param \App\Model\User $userModel
     * @param \Nette\Http\Session $session
     */
    public function __construct(\App\Model\User $userModel,
                                \Nette\Http\Session $session)
    {
        $this->session = $session;
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
        $this->template->user = $this->userModel->getUserData($this->user->id);
        $newAvatar = $this->session->getSection('newAvatar');
        $this->template->newAvatar = $newAvatar->status ?: FALSE;
        unset($newAvatar->status);
    }

    /**
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     */
    public function actionCrop($left, $top, $width, $height)
    {
        $this->userModel->cropPhoto($left, $top, $width, $height);
        $this->redirect('User:edit');
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
     * @return \App\Components\ChallengeForm
     */
    protected function createComponentUserPhotoForm()
    {
        $control = $this->userPhotoFormFactory->create($this->user->id);

        $control->getComponent('userPhotoForm')->onSuccess[] = function () {
            $this->session->getSection('newAvatar')->status = TRUE;
            $this->redirect('User:edit');
        };
        return $control;
    }

}
