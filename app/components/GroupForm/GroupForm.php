<?php

namespace App\Components;

use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * GroupForm component
 */
class GroupForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;

    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Group */
    protected $groupModel;

    /**
     * @param int $userId
     * @param \App\Model\User $userModel
     * @param \App\Model\Group $groupModel
     */
    public function __construct(
        $userId,
        \App\Model\User $userModel,
        \App\Model\Group $groupModel
    ) {
        $this->userId = $userId;
        $this->userModel = $userModel;
        $this->groupModel = $groupModel;
    }

    /**
     * @return Form
     */
    public function createComponentGroupForm()
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addText('name', 'Name')
            ->setRequired()
            ->addRule(Form::FILLED, '%label must be filled.')
            ->addRule(Form::MAX_LENGTH, '%label is too long', 100);

        $form->addText('telegram_group_id', 'Telegram Group ID')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 255);

        $form->addText('bot_token', 'Bot Token')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 255);

        $form->addTextArea('users', 'Invite your friends')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, '%label is too long', 1000);
        /** @var \Nette\Forms\Controls\BaseControl $usersControl */
        $usersControl = $form['users'];
        $usersControl->getControlPrototype()
            ->addAttributes(['data-role' => 'group-users'])
            ->addClass('js-group-users');

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->availableUsers = $this->userModel->getAvailableUsers(true);
        $this->template->currentUsers = isset($this->data['users']) ? $this->data['users'] : [];

        $isAdmin = false;
        if (!empty($this->data['id'])) {
            $isAdmin = $this->groupModel->isAdmin($this->data['id']);
        } else {
            $isAdmin = true;
        }
        $this->template->isAdmin = $isAdmin;

        if (!$this['groupForm']->offsetExists('submit')) {
            /** @var \Nette\Forms\Controls\SubmitButton $submitButton */
            $submitButton = $this['groupForm']->addSubmit('submit', 'Create');
            $submitButton->getControlPrototype()->addClass('btn btn-success');
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
        $data = (array) $values;
        if (empty($data['id'])) {
            $this->groupModel->createGroup($data);
            $presenter->flashMessage('New group has been created.', \App\Presenters\BasePresenter::MESSAGE_TYPE_SUCCESS);
        } else {
            if ($this->groupModel->updateGroup($data)) {
                $presenter->flashMessage('The group has been updated.', \App\Presenters\BasePresenter::MESSAGE_TYPE_SUCCESS);
            } else {
                $presenter->flashMessage('You do not have permission to update this group.', \App\Presenters\BasePresenter::MESSAGE_TYPE_ERROR);
            }
        }
    }
}
