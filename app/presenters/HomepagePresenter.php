<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Components\SignFormFactory;
use App\Components\RegistrationFormFactory;
use App\Model\User;
use \Nette\Utils\DateTime;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $signFormFactory;

    /** @var RegistrationFormFactory @inject */
    public $registrationFormFactory;

    public function renderDefault()
    {
        
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
        $form = $this->signFormFactory->create();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Welcome!', 'info');
            $form->getPresenter()->redirect('Main:');
        };
        return $form;
    }


    /**
     * Registration form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRegistrationForm()
    {
        $form = $this->registrationFormFactory->create();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Check your mail box and confirm the registration.', 'info');
            $form->getPresenter()->redirect('Homepage:');
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
