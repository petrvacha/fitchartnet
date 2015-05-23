<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Components\SignFormFactory;
use App\Model\User;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var SignFormFactory @inject */
    public $factory;

    /** @var User @inject */
    public $model;

    public function renderDefault()
    {

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
