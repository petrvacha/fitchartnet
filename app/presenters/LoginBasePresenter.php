<?php

namespace App\Presenters;


/**
 * Login Base presenter for all application presenters.
 */
abstract class LoginBasePresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
            $this->redirect('Homepage:default');
        }
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:default');
    }
}
