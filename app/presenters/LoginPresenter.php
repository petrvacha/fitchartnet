<?php

namespace App\Presenters;

use App\Model\Challenge;
use App\Model\Friend;
use App\Model\User;
use Fitchart\Application\Utilities;
use Nette;
use Nette\Http\IRequest;

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

    /** @var IRequest */
    protected $httpRequest;

    /** @var User */
    protected $userModel;

    /** @var Challenge */
    private $challengeModel;

    /** @var Friend */
    protected $friendModel;

    /**
     * LoginPresenter constructor.
     * @param IRequest $httpRequest
     * @param User $userModel
     * @param Challenge $challengeModel
     * @param Friend $friendModel
     */
    public function __construct(IRequest $httpRequest, User $userModel, Challenge $challengeModel, Friend $friendModel)
    {
        $this->httpRequest = $httpRequest;
        $this->userModel = $userModel;
        $this->challengeModel = $challengeModel;
        $this->friendModel = $friendModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'login';
        if ($this->getUser()->isLoggedIn()) {
            $challengeId = $this->challengeModel->getLastActiveUserChallenge();
            if ($challengeId) {
                $this->redirect('Challenge:detail', $challengeId->id);
            } else {
                $this->redirect('Challenge:default');
            }
        }
        $this->setLayout('authLayout');
    }

    public function renderResetPassword()
    {
        $this->template->title = 'reset password';
        if ($this->getUser()->isLoggedIn()) {
            $challengeId = $this->challengeModel->getLastActiveUserChallenge();
            if ($challengeId) {
                $this->redirect('Challenge:detail', $challengeId->id);
            } else {
                $this->redirect('Challenge:default');
            }
        }
        $this->setLayout('authLayout');
    }

    public function renderLast()
    {
        if ($this->getUser()->isLoggedIn()) {
            $challengeId = $this->challengeModel->getLastActiveUserChallenge();
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
            $challengeId = $this->challengeModel->getLastActiveUserChallenge();
            if ($challengeId) {
                $this->redirect('Challenge:detail', $challengeId->id);
            } else {
                $this->redirect('Challenge:default');
            }
        }
        $this->template->title = 'Fitchart.net';
        $this->template->randomNumber = rand(1, 3);
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
        $control->getComponent('signForm')->onSuccess[] = function () {
            $challengeId = $this->httpRequest->getCookie('invitationChallenge');
            $hash = $this->httpRequest->getCookie('invitationHash');
            $httpResponse = $this->getHttpResponse();
            $httpResponse->deleteCookie('invitationChallenge');
            $httpResponse->deleteCookie('invitationHash');

            if ($hash && $challengeId) {
                $challenge = $this->challengeModel->findRow($challengeId);
                if ($hash === Utilities::generateInvitationHash($challengeId, $challenge->created_at)) {
                    $this->friendModel->addFriend($challenge->created_by, $this->user->getIdentity()->id);
                    $this->challengeModel->addUserToChallenge($challengeId, $this->user->getIdentity()->id, $challenge->created_by);
                    $this->flashMessage('The challenge is waiting...', parent::MESSAGE_TYPE_INFO);
                }
            } else {
                $challengeId = $this->challengeModel->getLastActiveUserChallenge();
                if ($challengeId) {
                    $this->flashMessage('This is your challenge! Finish it!', 'info');
                    $this->redirect('Challenge:detail', ['id' => $challengeId->id]);
                } else {
                    $this->flashMessage('Welcome on board!', 'info');
                    $this->redirect('Challenge:');
                }
            }
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
        $control->getComponent('resetPasswordForm')->onSuccess[] = function () {
            $this->flashMessage('Check your spam/mail box.', 'info');
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
        $control->getComponent('newPasswordForm')->onSuccess[] = function () {
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
        // TODO: LaunchAlertForm component needs to be created
        $form = new \Nette\Application\UI\Form();
        return $form;
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.', 'info');
        $this->redirect('in');
    }

    public function actionLogout()
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
        $this->setLayout('authLayout');
    }

    public function renderResetError()
    {
    }
}
