<?php

namespace App\Presenters;

use App\Model\Group;
use App\Model\GroupUser;
use App\Model\Notification;

/**
 * Group presenter
 */
class GroupPresenter extends LoginBasePresenter
{
    /** @var Group */
    protected $groupModel;

    /** @var GroupUser */
    protected $groupUserModel;

    /** @var \App\Components\GroupForm\IGroupFormFactory @inject */
    public $groupFormFactory;

    /**
     * @param Notification $notificationModel
     * @param Group $groupModel
     * @param GroupUser $groupUserModel
     */
    public function __construct(
        Notification $notificationModel,
        Group $groupModel,
        GroupUser $groupUserModel
    ) {
        parent::__construct($notificationModel);
        $this->groupModel = $groupModel;
        $this->groupUserModel = $groupUserModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'Groups';
        $this->template->groups = $this->groupModel->getGroupsForUser();
    }

    /**
     * @param int $id
     */
    public function renderDetail($id)
    {
        if (!$this->groupModel->isMember($id)) {
            $this->flashMessage('You are not a member of this group.', parent::MESSAGE_TYPE_INFO);
            $this->redirect('Group:default');
        }

        $group = $this->groupModel->findRow($id);
        $this->template->title = $group->name;
        $this->template->group = $group;
        $this->template->members = $this->groupUserModel->getMembers($id);
        $this->template->isAdmin = $this->groupModel->isAdmin($id);
    }

    public function renderCreate()
    {
        $this->template->title = 'Create Group';
        $this['groupForm']['groupForm']
            ->addSubmit('submit', 'Create')
            ->getControlPrototype()
            ->addClass('btn btn-success');
    }

    /**
     * @param int $id
     */
    public function renderEdit($id)
    {
        if (!$this->groupModel->isAdmin($id)) {
            $this->flashMessage('Only the group admin can edit the group.', parent::MESSAGE_TYPE_INFO);
            $this->redirect('Group:default');
        }

        $data = $this->groupModel->getGroupData($id);
        if (!$data) {
            $this->flashMessage('Group not found.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Group:default');
        }
        $this['groupForm']->setData($data);
        unset($data['users']);
        $this['groupForm']['groupForm']->setDefaults($data);
        $this['groupForm']['groupForm']
            ->addSubmit('submit', 'Save')
            ->getControlPrototype()
            ->addClass('btn btn-success');
    }

    /**
     * @param int $groupId
     */
    public function actionJoin($groupId)
    {
        if (!$this->groupModel->isMember($groupId)) {
            $this->flashMessage('You have not been invited to this group.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Group:default');
        }
        $this->groupUserModel->attend($groupId, true);
        $this->flashMessage('You have joined the group.', parent::MESSAGE_TYPE_SUCCESS);
        $this->redirect('Group:detail', ['id' => $groupId]);
    }

    /**
     * @param int $groupId
     */
    public function actionLeave($groupId)
    {
        if ($this->groupModel->isAdmin($groupId)) {
            $this->flashMessage('Group admin cannot leave the group.', parent::MESSAGE_TYPE_ERROR);
            $this->redirect('Group:default');
        }

        $this->groupUserModel->attend($groupId, false);
        $this->flashMessage('You have left the group.', parent::MESSAGE_TYPE_INFO);
        $this->redirect('Group:default');
    }

    protected function createComponentGroupForm()
    {
        $control = $this->groupFormFactory->create($this->user->id);
        $control->getComponent('groupForm')->onSuccess[] = function () {
            $this->redirect('Group:default');
        };
        return $control;
    }
}
