<?php

namespace App\Model;

class GroupUser extends BaseModel
{
    /** @var \Nette\Security\User */
    protected $user;

    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     */
    public function __construct(
        \Nette\Database\Context $context,
        \Nette\Security\User $user
    ) {
        parent::__construct($context);
        $this->user = $user;
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @param bool $active
     * @param int|null $invitedByUserId
     */
    public function addUser($groupId, $userId, $active = false, $invitedByUserId = null)
    {
        if (!$invitedByUserId) {
            $invitedByUserId = $this->getCurrentUserId();
        }

        if (!$this->findBy(['group_id' => $groupId, 'user_id' => $userId])->fetch()) {
            $this->insert([
                'group_id' => $groupId,
                'user_id' => $userId,
                'invited_by' => $invitedByUserId,
                'invited_at' => $this->getDateTime(),
                'active' => $active
            ]);
        }
    }

    /**
     * @param int $groupId
     * @param int $userId
     */
    public function removeUser($groupId, $userId)
    {
        $this->findBy(['group_id' => $groupId, 'user_id' => $userId])->delete();
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function getMembers($groupId)
    {
        return $this->findBy(['group_id' => $groupId])->fetchAll();
    }

    /**
     * @param int $groupId
     * @param bool $active
     */
    public function attend($groupId, $active = true)
    {
        $this->findBy(['group_id' => $groupId, 'user_id' => $this->getCurrentUserId()])
            ->update(['active' => $active]);
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
