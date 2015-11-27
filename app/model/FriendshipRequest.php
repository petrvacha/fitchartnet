<?php

namespace App\Model;


class FriendshipRequest extends BaseModel
{
    /** @var Nette\Security\User $user */
    protected $user;

    /** @var Nette\Security\User $friendModel */
    protected $friendModel;


    /**
     * @param \Nette\Database\Context $context,
     * @param \Nette\Security\User $user
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                \App\Model\Friend $friendModel)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->friendModel = $friendModel;
    }

    /**
     * @param int $toUserId
     * @return mixed
     */
    public function addFriendshipRequest($toUserId)
    {
        $fromUserId = $this->user->getIdentity()->id;

        $sameRequest = $this->findBy(['approved' => NULL, 'from_user_id' => $fromUserId, 'to_user_id' => $toUserId])->fetch();

        if (!$sameRequest) {
            $insertData = [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'created_at' => $this->getDateTime(),
                'updated_at' => $this->getDateTime()
            ];
            return $this->insert($insertData);
        }
        return FALSE;
    }


    /**
     * @param int $userId
     * @param bool $approve
     * @return mixed
     */
    public function acceptFriendshipRequest($userId, $approve = TRUE)
    {
        $toUserId = $this->user->getIdentity()->id;
        $updateData = ['approved' => $approve, 'updated_at' => $this->getDateTime()];
        $updated = $this->findBy(['approved' => NULL, 'from_user_id' => $userId, 'to_user_id' => $toUserId])->update($updateData);

        if ($updated && $approve) {
            return $this->friendModel->addFriend($userId);
        }
        return FALSE;
    }

    /**
     * @param int $toUserId
     * @return mixed
     */
    public function removeFriendshipRequest($toUserId)
    {
        $fromUserId = $this->user->getIdentity()->id;
        return $this->findBy(['approved' => NULL, 'from_user_id' => $fromUserId, 'to_user_id' => $toUserId])->delete();
    }
}