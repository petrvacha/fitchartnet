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

    /** @var \App\Model\Challenge */
    protected $challengeModel;

    /** @var \App\Model\ActivityLog */
    protected $activityLogModel;

    /** @var int */
    protected $challengeId;


    /**
     * @param int $userId
     * @param int $challengeId
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     * @param \App\Model\ActivityLog $activityLogModel
     */
    public function __construct($userId,
                                $challengeId,
                                \App\Model\User $userModel,
                                \App\Model\Activity $activityModel,
                                \App\Model\Challenge $challengeModel,
                                \App\Model\ActivityLog $activityLogModel)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->userModel = $userModel;
        $this->activityModel = $activityModel;
        $this->challengeModel = $challengeModel;
        $this->activityLogModel = $activityLogModel;
        $this->challengeId = $challengeId; //$session->getSection('challenge')->showActivitySelect;
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

        if (empty($this->challengeId)) {
            $form->addSelect('activity_id', 'Activity', $this->activityModel->getList());
        }
        $form->setDefaults($this->data);

        $form->addSubmit('submit', 'Add');

        $form->onSuccess[] = array($this, 'formSent');

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->showActivitySelect = empty($this->challengeId);
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

        if (!empty($this->challengeId)) {
            $challenge = $this->challengeModel->findRow($this->challengeId);
            $values['activity_id'] = $challenge['activity_id'];
        }
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