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
    

    public function renderDefault()
    {
        $this->template->title = 'Fitchart - gamma version';
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
            $this->redirect('Main:');
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
