<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;

/**
 * ActivityForm component
 */
class ActivityForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;
    
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Activity */
    protected $activityModel;

    /** @var \App\Model\ActivityLog */
    protected $activityLogModel;
    

    /**
     * @param int $userId
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     * @param \App\Model\ActivityLog $activityLogModel
     */
    public function __construct($userId,
                                \App\Model\User $userModel,
                                \App\Model\Activity $activityModel,
                                \App\Model\ActivityLog $activityLogModel)
    {
        parent::__construct();
        $this->userId = $userId;
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
            ->getControlPrototype()->addClass('datetimepicker');

        $form['created_at']->getLabelPrototype()->style = 'float: left; width: 60px;';

        $form->addText('value', 'Count')
            ->setAttribute('placeholder', '0')
            ->setRequired('You forget fill important number.')
            ->addRule(Form::INTEGER, 'Wrong format. Input must be an integer.');
        $form['value']->getLabelPrototype()->style = 'float: left; width: 60px;';

        $form->addSelect('activity_id', 'Activity', $this->activityModel->getList());

        if (isset($this->data['id'])) {
            $form->setDefaults($this->data);
        } else {
            if (empty($this->data['create_at'])) {
                $this->data['create_at'] = date('Y/m/d H:00:00');
            }
            $form->setDefaults($this->data);
        }
        $form->addSubmit('submit', 'Add');

        $form->onSuccess[] = array($this, 'formSent');
        
        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->time = date('Y/m/d H:00'); //@todo trunc minutes down
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        $values['user_id'] = $this->userId;

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