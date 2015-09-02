<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class LaunchAlertForm extends \Fitchart\Application\Control
{
    /** @var \App\Model\LunchAlert */
    protected $launchAlertModel;
   

    /**
     * @param \App\Model\LunchAlert $launchAlertModel
     */
    public function __construct(\App\Model\LaunchAlert $launchAlertModel)
    {
        parent::__construct();
        $this->launchAlertModel = $launchAlertModel;
    }

    /**
     * @return Form
     */
    public function createComponentLaunchAlertForm()
    {
        $form = new Form;
        
        $form->addText('email', 'Email')
            ->addRule(Form::EMAIL, 'Doesn\'t look like a valid email eee.')
            ->setAttribute('placeholder', 'Email Address');

        $form->addSubmit('submit', 'Sign Up')
            ->getControlPrototype()->class = 'btn btn-success';

        $form->onSuccess[] = array($this, 'formSent');
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        $this->launchAlertModel->insertEmail($values);
    }
}