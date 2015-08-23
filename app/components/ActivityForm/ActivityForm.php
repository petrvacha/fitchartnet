<?php

namespace App\Components;

use Nette;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;


class ActivityForm extends \Pushupers\Application\Control
{
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Activity */
    protected $activityModel;

    /** @var \App\Model\ActivityLog */
    protected $activityLogModel;
    

    /**
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     */
    public function __construct(\App\Model\User $userModel,
                                \App\Model\Activity $activityModel,
                                \App\Model\ActivityLog $activityLogModel
                                )
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->activityModel = $activityModel;
        $this->activityLogModel = $activityLogModel;
    }


    /**
     * @return Form
     */
    public function createComponentActivityForm()
    {
        $form = new Form;
        $form->addText('created_at', 'Time')
            ->setRequired('Please enter your username.');

        $form->addText('value', 'Count')
            ->setRequired('You forget fill important number.')
            ->addRule(Form::INTEGER, 'Wrong format. Input must be an integer.');

        //$form->addSelect('activity_id', 'Activity', $this->activityModel->getList());


        $form->addSubmit('submit', 'Add');

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
        $values['user_id'] = $this->presenter->getUser()->getId();
        $values['activity_id'] = 1;
        
        if (empty($values['id'])) {
            $values['updated_at'] = $values['created_at'];
            $this->activityLogModel->insert($values);
        } else {
            $this->activityLogModel->update($values);
        }
    }
}