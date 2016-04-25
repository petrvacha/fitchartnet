<?php

namespace App\Presenters;

use Nette;

/**
 * Homepage presenter
 */
class HomepagePresenter extends BasePresenter
{
    /** @var \App\Components\SignForm\ISignFormFactory @inject */
    public $signFormFactory;

    /** @var \App\Components\RegistrationForm\IRegistrationFormFactory @inject */
    public $registrationFormFactory;

    /** @var \App\Components\LaunchAlertForm\ILaunchAlertFormFactory @inject */
    public $launchAlertFormFactory;


    /** @var \Kdyby\Facebook\Facebook */
    private $facebook;

    /** @var \App\Model\User */
    private $userModel;

    /**
     * You can use whatever way to inject the instance from DI Container,
     * but let's just use constructor injection for simplicity.
     *
     * Class UsersModel is here only to show you how the process should work,
     * you have to implement it yourself.
     */
    public function __construct(\Kdyby\Facebook\Facebook $facebook, \App\Model\User $userModel)
    {
        parent::__construct();
        $this->facebook = $facebook;
        $this->userModel = $userModel;
    }


    /**
     * @return \Kdyby\Facebook\Dialog\LoginDialog
     */
    protected function createComponentFbLogin()
    {
        /** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */
        $dialog = $this->facebook->createDialog('login');

        $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
            $fb = $dialog->getFacebook();

            if (!$fb->getUser()) {
                $this->flashMessage("Facebook authentication failed.");
                return;
            }

            /**
             * If we get here, it means that the user was recognized
             * and we can call the Facebook API
             */

            try {
                $me = $fb->api('/me', NULL, ['fields' => [
                    'id',
                    'first_name',
                    'last_name',
                    'picture.type(large)',
                    'email',
                ]]);
                
                if (!$existing = $this->userModel->findByFacebookId($fb->getUser())) {
                    /**
                     * Variable $me contains all the public information about the user
                     * including facebook id, name and email, if he allowed you to see it.
                     */
                    $existing = $this->userModel->registerFromFacebook($me);
                }

                /**
                 * You should save the access token to database for later usage.
                 *
                 * You will need it when you'll want to call Facebook API,
                 * when the user is not logged in to your website,
                 * with the access token in his session.
                 */
                $this->userModel->updateFacebookAccessToken($fb->getUser(), $fb->getAccessToken());

                /**
                 * Nette\Security\User accepts not only textual credentials,
                 * but even an identity instance!
                 */
                $data = $this->userModel->getUserLoginData($existing->id);
                $this->user->login(new \Nette\Security\Identity($data['id'], $data['role'], $data));

                /**
                 * You can celebrate now! The user is authenticated :)
                 */

            } catch (\Kdyby\Facebook\FacebookApiException $e) {
                /**
                 * You might wanna know what happened, so let's log the exception.
                 *
                 * Rendering entire bluescreen is kind of slow task,
                 * so might wanna log only $e->getMessage(), it's up to you
                 */
                \Tracy\Debugger::log($e, 'facebook');
                $this->flashMessage("Facebook authentication failed hard.");
            }

            $this->redirect('Challenge:');
        };

        return $dialog;
    }

    public function renderDefault()
    {
        $this->template->title = 'Gamma version';
    }

    public function renderLaunch()
    {
        $this->template->title = 'Fitchart.net';
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
     * Registration form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRegistrationForm()
    {
        $control = $this->registrationFormFactory->create();
        $control->getComponent('registrationForm')->onSuccess[] = function() {
            $this->flashMessage('Check your mail box and confirm the registration.', 'info');
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

}
