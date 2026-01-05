<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;

class UserWeightForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;
    
    /** @var \App\Model\Weight */
    protected $weightModel;


    /**
     * @param int $userId
     * @param \App\Model\Weight $userWeight
     */
    public function __construct($userId,
                                \App\Model\Weight $userWeight)
    {
        $this->userId = $userId;
        $this->weightModel = $userWeight;
    }

    /**
     * @return Form
     */
    public function createComponentUserWeightForm()
    {
        $form = new Form;
        $form->addText('value', 'Weight')
            ->addRule(Form::FILLED, '%label must be filled')
            ->addRule(Form::FLOAT, '%label must be number')
            ->addRule(Form::MAX_LENGTH, '%label is way too long', 4)
            ->setAttribute('placeholder', 'new weight');

        $form->addSubmit('submit', 'Update');
        $form->onSuccess[] = array($this, 'formSent');
        
        $this->addBootstrapStyling($form);
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
        try {
            $values['user_id'] = $this->userId;
            $this->weightModel->insertWeight($values);
            
        } catch (\Fitchart\Application\SecurityException $e) {
            $form->addError($e->getMessage());
        }
    }
    
}