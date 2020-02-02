<?php

namespace App\Presenters;

use App\Model\Challenge;
use App\Model\User;
use Nette;

/**
 * Login presenter
 */
class LoginPresenter extends BasePresenter
{
    /** @var \App\Components\SignForm\ISignFormFactory @inject */
    public $signFormFactory;

    /** @var \App\Components\ResetPasswordForm\IResetPasswordFormFactory @inject */
    public $resetPasswordFormFactory;

    /** @var \App\Components\NewPasswordForm\INewPasswordFormFactory @inject */
    public $newPasswordFormFactory;

    /** @var Challenge */
    private $challengeModel;

    /** @var User */
    protected $userModel;


    /**
     * @param User $userModel
     */
    public function __construct(User $userModel, Challenge $challengeModel)
    {
        $this->userModel = $userModel;
        $this->challengeModel = $challengeModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'login';
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Challenge:default');
        }
        $this->setLayout('authLayout');
    }

    public function renderResetPassword()
    {
        $this->template->title = 'reset password';
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Challenge:default');
        }
        $this->setLayout('authLayout');
    }

    public function renderLast()
    {
        if ($this->getUser()->isLoggedIn()) {
            $challengeId = $this->challengeModel->getLastUserChallenge();
            if ($challengeId) {
                $this->redirect('Challenge:detail', $challengeId->id);
            } else {
                $this->redirect('Challenge:default');
            }
        }
        $this->redirect('Homepage:default');
    }

    public function renderLaunch()
    {
        if ($this->getUser()->isLoggedIn()) {
                $this->redirect('Challenge:default');
        }
        $this->template->title = 'Fitchart.net';
        $this->template->randomNumber = rand(1,3);
        $this->setLayout('launch');
    }

    public function renderRegistred()
    {

    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $control = $this->signFormFactory->create();
        $control->getComponent('signForm')->onSuccess[] = function() {
            $this->flashMessage('Welcome on board!', 'info');
            $this->redirect('Challenge:');
        };
        return $control;
    }

    /**
     * Reset Password form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentResetPasswordForm()
    {
        $control = $this->resetPasswordFormFactory->create();
        $control->getComponent('resetPasswordForm')->onSuccess[] = function() {
            $this->flashMessage('Check your mail box.', 'info');
            $this->redirect('Login:resetPassword');
        };
        return $control;
    }

    /**
     * Reset Password form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentNewPasswordForm()
    {
        $token = str_replace('/new-password/', '', $this->getHttpRequest()->getUrl()->path);
        $token = str_replace('/', '', $token);
        $control = $this->newPasswordFormFactory->create($token);
        $control->getComponent('newPasswordForm')->onSuccess[] = function() {
            $this->flashMessage('Your password has been changed.', 'info');
            $this->redirect('Homepage:');
        };
        return $control;
    }

    /**
     * Launch form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentLaunchAlertForm()
    {
        $form = $this->launchAlertFormFactory->create();

        $form->onSuccess[] = function () {
            $this->redirect('Main:default');
        };
        return $form;
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.', 'info');
        $this->redirect('in');
    }

    public function actionNewPassword($token)
    {
        $result = $this->userModel->checkToken($token);

        if (!$result) {
            $this->flashMessage('We are sorry, your reset link is wrong.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:resetError');
        }
    }

    public function renderResetError()
    {

    }
}
