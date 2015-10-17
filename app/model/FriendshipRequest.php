<?php

namespace App\Model;


class FriendshipRequest extends BaseModel
{
    /** @var Nette\Security\User $user */
    protected $user;


    /**
     * @param \Nette\Database\Context $context,
     * @param \Nette\Security\User $user
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user)
    {
        parent::__construct($context);
        $this->user = $user;
    }

    /**
     * @param int $toUserId
     */
    public function addfriendshipRequest($toUserId)
    {
        $id = $this->user->getIdentity()->id;

        $insertData = [
            'from_user_id' => $id,
            'to_user_id' => $toUserId,
            'created_at' => $this->getDateTime(),
            'updated_at' => $this->getDateTime()
                ];
        $this->insert($insertData);
    }


    /**
     * @param int $id
     * @param bool|TRUE $approve
     */
    public function approveFiendshipRequest($userId, $approve = TRUE)
    {
        $toUserId = $this->user->getIdentity()->id;

        $updateData = ['approved' => $approve, 'updated_at' => $this->getDateTime()];

        $this->findBy(['id' => $userId, 'to_user_id' => $toUserId])->update($updateData);
    }

}