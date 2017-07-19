<?php

namespace App\Components;

use Nette\Utils\ArrayHash;
use Nette\Application\UI\Form;

/**
 * ChallengeForm component
 */
class ChallengeForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;

    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Activity */
    protected $activityModel;

    /** @var \App\Model\Challenge */
    protected $challengeModel;


    /**
     * @param int $userId
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     * @param \App\Model\Challenge $challengeModel
     */
    public function __construct($userId,
                                \App\Model\User $userModel,
                                \App\Model\Activity $activityModel,
                                \App\Model\Challenge $challengeModel)
    {
        parent::__construct();
        $this->userId = $userId;
        $this->userModel = $userModel;
        $this->activityModel = $activityModel;
        $this->challengeModel = $challengeModel;
    }

    /**
     * @return Form
     */
    public function createComponentChallengeForm()
    {
        $form = new Form;

        $form->addHidden('id');

        $form->addText('name', 'Name')
            ->setRequired()
            ->addRule(Form::FILLED, '%label must be filled.')
            ->addRule(Form::MAX_LENGTH, '%label is too long', 50);

        $form->addTextArea('description', 'Description')
            ->setRequired(FALSE)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 1000);

        $form->addSelect('activity_id', 'Activity', $this->activityModel->getList());

        $form->addText('start_at', 'Start')
            ->setAttribute('placeholder', 'now')
            ->getControlPrototype()->addClass('datetimepicker start');

        $form->addText('end_at', 'End')
            ->setAttribute('placeholder', 'end of this month')
            ->getControlPrototype()->addClass('datetimepicker end');

        $form->addText('final_value', 'Final value')
            ->setAttribute('placeholder', '0')
            ->setRequired()
            ->addRule(Form::FILLED, '%label must be filled.')
            ->addRule(Form::INTEGER, '%label must be an integer.');

        $form->addTextArea('users', 'Invite your friends')
            ->setRequired(FALSE)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 1000);

        $form->onSuccess[] = array($this, 'formSent');

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->availableUsers = $this->userModel->getAvailableUsers(TRUE);
        $this->template->currentUsers = isset($this->data['users']) ? $this->data['users'] : [];
        $this->template->timeEndOfMonth = date('Y/m/t');

        if (!$this['challengeForm']->offsetExists('submit')) {
            $this['challengeForm']->addSubmit('submit', 'Create')
                ->getControlPrototype()
                ->addClass('btn btn-success');
        }
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        $presenter = $this->getPresenter();
        if (empty($values['id'])) {
            $this->challengeModel->createNewChallenge($values);
            $presenter->flashMessage('New challenge has been created.', $presenter::MESSAGE_TYPE_SUCCESS);
        } else {
            $this->challengeModel->updateChallenge($values);
            $presenter->flashMessage('The challenge has been updated.', $presenter::MESSAGE_TYPE_SUCCESS);
        }
    }

}