<?php


namespace App\Presenters;


class RegistrationPresenter extends BasePresenter
{
    /** @var \App\Model\User */
    protected $userModel;


    /**
     * @param \App\Model\User $userModel
     */
    public function __construct(\App\Model\User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
    }

    public function renderError()
    {

    }

    /**
     * @param string $hash
     */
    public function actionCheck($hash)
    {
        $result = $this->userModel->activeUserByToken($hash);

        if ($result) {
            $this->flashMessage('Congratulations, your account has been activated!', parent::MESSAGE_TYPE_INFO);
            $this->redirect('Homepage:default');
        } else {
            $this->flashMessage('We are sorry, your activated link is wrong.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Registration:error');
        }
    }
}