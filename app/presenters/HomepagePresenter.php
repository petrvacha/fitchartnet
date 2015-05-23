<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Components\SignFormFactory;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $factory;

    public function renderDefault()
    {
        $this->template->anyVariable = 'any value';
    }


    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = $this->factory->create();
        $form->onSuccess[] = function ($form) {
            $form->getPresenter()->redirect('Homepage:');
        };
        return $form;
    }


    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.');
        $this->redirect('in');
    }

}
