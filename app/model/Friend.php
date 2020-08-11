<?php

namespace App\Model;


class Friend extends BaseModel
{
    /** @var \App\Model\Role $user */
    protected $user;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user)
    {
        parent::__construct($context);
        $this->user = $user;
    }

    /**
     * @param $userId
     * @param null $userId2
     * @return bool|\Nette\Database\Table\IRow
     */
    public function addFriend($userId, $userId2 = NULL)
    {
        if (!$userId2) {
            $userId2 = $this->user->getIdentity()->id;
        }

        $user = $this->context->table('user')->where(['id' => $userId])->fetch();
        if ($user) {
            $this->getTable()->insert(['user_id' => $userId, 'user_id2' => $userId2]);
            $this->getTable()->insert(['user_id' => $userId2, 'user_id2' => $userId]);
        }
        return $user;
    }

    /**
     * @param int $userId
     */
    public function removeFriend($userId)
    {
        $userId2 = $this->user->getIdentity()->id;
        $this->findBy(['user_id' => $userId, 'user_id2' => $userId2])->delete();
        $this->findBy(['user_id' => $userId2, 'user_id2' => $userId])->delete();
    }

    /**
     * @param int $userId
     * @param int|NULL $userId2
     * @return mixed
     */
    public function areFriends($userId, $userId2 = NULL)
    {
        if ($userId2 === NULL) {
            $userId2 = $this->user->getIdentity()->id;
        }

        return $this->findBy(['user_id' => $userId, 'user_id2' => $userId2])->fetch();
    }

}