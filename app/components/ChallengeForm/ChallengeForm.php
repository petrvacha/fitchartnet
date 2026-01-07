<?php

namespace App\Components;

use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

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
    public function __construct(
        $userId,
        \App\Model\User $userModel,
        \App\Model\Activity $activityModel,
        \App\Model\Challenge $challengeModel
    ) {
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
        $form = new Form();

        $form->addHidden('id');

        $form->addText('name', 'Name')
            ->setRequired()
            ->addRule(Form::FILLED, '%label must be filled.')
            ->addRule(Form::MAX_LENGTH, '%label is too long', 50);

        $form->addTextArea('description', 'Description')
            ->setRequired(false)
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
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 1000);

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->availableUsers = $this->userModel->getAvailableUsers(true);
        $this->template->currentUsers = isset($this->data['users']) ? $this->data['users'] : [];

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
        if (!$presenter) {
            return;
        }
        if (empty($values['id'])) {
            $this->challengeModel->createNewChallenge($values);
            $presenter->flashMessage('New challenge has been created.', \App\Presenters\BasePresenter::MESSAGE_TYPE_SUCCESS);
        } else {
            $this->challengeModel->updateChallenge($values);
            $presenter->flashMessage('The challenge has been updated.', \App\Presenters\BasePresenter::MESSAGE_TYPE_SUCCESS);
        }
    }
}
