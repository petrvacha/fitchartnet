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
            ->setAttribute('placeholder', 'now')
            ->getControlPrototype()->class = 'datetimepicker';

        $form->addText('value', 'Count')
            ->setAttribute('placeholder', '0')
            ->setRequired('You forget fill important number.')
            ->addRule(Form::INTEGER, 'Wrong format. Input must be an integer.');

        //$form->addSelect('activity_id', 'Activity', $this->activityModel->getList());

        if (isset($this->data['id'])) {
            $form->setDefaults($this->data);
        } else {
            if (empty($this->data['create_at'])) {
                $this->data['create_at'] = date('Y/m/d H:00:00');
            }
            $form->setDefaults($this->data);
        }
        $form->addSubmit('submit', 'Add')
            ->getControlPrototype()->class = 'btn btn-success';

        $form->onSuccess[] = array($this, 'formSent');
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->time = date('Y/m/d H:00');
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
            if (empty($values['created_at'])) {
                $values['created_at'] = new \DateTime;
            }
            $values['updated_at'] = $values['created_at'];
            $this->activityLogModel->insert($values);
        } else {
            $this->activityLogModel->update($values);
        }
    }
}