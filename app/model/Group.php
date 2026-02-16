<?php

namespace App\Model;

class Group extends BaseModel
{
    /** @var string */
    protected $tableName = 'group';

    /** @var \Nette\Security\User */
    protected $user;

    /** @var User */
    protected $userModel;

    /** @var GroupUser */
    protected $groupUserModel;

    /** @var Notification */
    protected $notificationModel;

    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param User $userModel
     * @param GroupUser $groupUserModel
     * @param Notification $notificationModel
     */
    public function __construct(
        \Nette\Database\Context $context,
        \Nette\Security\User $user,
        User $userModel,
        GroupUser $groupUserModel,
        Notification $notificationModel
    ) {
        parent::__construct($context);
        $this->user = $user;
        $this->userModel = $userModel;
        $this->groupUserModel = $groupUserModel;
        $this->notificationModel = $notificationModel;
    }

    /**
     * @param array $data
     * @return \Nette\Database\Table\ActiveRow
     */
    public function createGroup($data)
    {
        $userId = $this->getCurrentUserId();

        $groupData = [
            'name' => $data['name'],
            'admin_user_id' => $userId,
            'telegram_group_id' => !empty($data['telegram_group_id']) ? $data['telegram_group_id'] : null,
            'bot_token' => !empty($data['bot_token']) ? $data['bot_token'] : null,
            'created_at' => $this->getDateTime(),
            'updated_at' => $this->getDateTime(),
        ];

        $row = $this->insert($groupData);

        $this->groupUserModel->addUser($row->id, $userId, true);

        if (!empty($data['users'])) {
            $this->addUsersToGroup($row->id, $data['users']);
        }

        return $row;
    }

    /**
     * @param array $data
     * @return bool true on success, false when group not found or user is not the admin
     */
    public function updateGroup($data)
    {
        $id = $data['id'];
        $group = $this->findRow($id);

        if (!$group || (int) $group->admin_user_id !== (int) $this->getCurrentUserId()) {
            return false;
        }

        $updateData = [
            'name' => $data['name'],
            'telegram_group_id' => !empty($data['telegram_group_id']) ? $data['telegram_group_id'] : null,
            'bot_token' => !empty($data['bot_token']) ? $data['bot_token'] : null,
            'updated_at' => $this->getDateTime(),
        ];

        $group->update($updateData);

        if (isset($data['users'])) {
            $this->updateUsersInGroup($id, $data['users']);
        }

        return true;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getGroupData($id)
    {
        $data = $this->findOneBy(['id' => $id]);
        if ($data) {
            $data = $data->toArray();
            $users = $this->groupUserModel->findBy(['group_id' => $id])->fetchAll();
            $data['users'] = [];
            foreach ($users as $user) {
                $data['users'][] = ['id' => $user->user_id, 'name' => $user->user->username];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getGroupsForUser()
    {
        $userId = $this->getCurrentUserId();
        return $this->context->query(
            "SELECT g.*, gu.active as membership_active
             FROM `group` g
             JOIN group_user gu ON gu.group_id = g.id
             WHERE gu.user_id = ?
             ORDER BY g.name ASC",
            $userId
        )->fetchAll();
    }

    /**
     * Groups where the user is a member and Telegram (bot_token, telegram_group_id) is configured.
     *
     * @param int $userId
     * @return array
     */
    public function getGroupsWithTelegramForUser($userId)
    {
        return $this->context->query(
            "SELECT g.*
             FROM `group` g
             JOIN group_user gu ON gu.group_id = g.id
             WHERE gu.user_id = ?
             AND g.telegram_group_id IS NOT NULL
             AND g.bot_token IS NOT NULL
             AND g.bot_token != ''
             ORDER BY g.name ASC",
            $userId
        )->fetchAll();
    }

    /**
     * @param int $groupId
     * @param int|null $userId
     * @return bool
     */
    public function isAdmin($groupId, $userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }
        $group = $this->findRow($groupId);
        return $group && (int) $group->admin_user_id === (int) $userId;
    }

    /**
     * @param int $groupId
     * @param int|null $userId
     * @return bool
     */
    public function isMember($groupId, $userId = null)
    {
        if ($userId === null) {
            $userId = $this->getCurrentUserId();
        }
        return (bool) $this->groupUserModel->findBy(['group_id' => $groupId, 'user_id' => $userId])->fetch();
    }

    /**
     * @param int $groupId
     * @param string $users
     */
    public function addUsersToGroup($groupId, $users)
    {
        foreach (array_unique(explode(',', $users)) as $userIdStr) {
            $userId = (int) $userIdStr;
            if ($userId > 0 && $this->userModel->hasPermissionForUser($userId)) {
                $this->groupUserModel->addUser($groupId, $userId);
                $this->notificationModel->insertNotification(Notification::MESSAGE_NEW_GROUP_INVITATION, $userId);
            }
        }
    }

    /**
     * @param int $groupId
     * @param string $users
     */
    private function updateUsersInGroup($groupId, $users)
    {
        $newUserIds = array_unique(explode(',', $users));
        $oldUsers = $this->groupUserModel
            ->findBy(['group_id' => $groupId])
            ->select('user_id')
            ->fetchAll();
        $oldUserIds = [];
        foreach ($oldUsers as $oldUser) {
            $oldUserIds[] = $oldUser['user_id'];
        }

        foreach ($newUserIds as $userIdStr) {
            $userId = (int) $userIdStr;
            if ($userId > 0 && $this->userModel->hasPermissionForUser($userId) && !in_array($userId, $oldUserIds)) {
                $this->groupUserModel->addUser($groupId, $userId);
                $this->notificationModel->insertNotification(Notification::MESSAGE_NEW_GROUP_INVITATION, $userId);
            }
        }

        $adminUserId = $this->getCurrentUserId();
        foreach ($oldUserIds as $oldUserId) {
            if (!in_array($oldUserId, $newUserIds) && $oldUserId != $adminUserId) {
                $this->groupUserModel->removeUser($groupId, $oldUserId);
            }
        }
    }

    /**
     * @return int
     * @throws \Nette\Security\AuthenticationException when user is not logged in
     */
    private function getCurrentUserId()
    {
        $identity = $this->user->getIdentity();
        if ($identity === null) {
            throw new \Nette\Security\AuthenticationException('User is not logged in.');
        }
        return (int) $identity->getId();
    }
}
