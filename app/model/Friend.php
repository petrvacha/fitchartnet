<?php

namespace App\Model;


class Friend extends BaseModel
{
    /** @var \App\Model\Role $roleModel */
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
     * @param int $userId
     */
    public function addFriend($userId)
    {
        $userId2 = $this->user->getIdentity()->id;
        $this->getTable()->insert(['user_id' => $userId, 'user_id2' => $userId2]);
        $this->getTable()->insert(['user_id' => $userId2, 'user_id2' => $userId]);
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
    
}