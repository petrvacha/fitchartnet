<?php

namespace App\Model;


class FriendshipRequest extends BaseModel
{
    /** @var \Nette\Security\User $user */
    protected $user;

    /** @var Friend $friendModel */
    protected $friendModel;

    /** @var Notification $notificationModel */
    protected $notificationModel;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param Friend $friendModel
     * @param Notification $notificationModel
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                Friend $friendModel,
                                Notification $notificationModel)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->friendModel = $friendModel;
        $this->notificationModel = $notificationModel;
    }

    /**
     * @param int $toUserId
     * @return mixed
     */
    public function addFriendshipRequest($toUserId)
    {
        $fromUserId = $this->user->getIdentity()->id;

        $sameRequest = $this->getTable()->where("from_user_id = ? AND to_user_id = ? OR from_user_id = ? AND to_user_id = ?", $fromUserId, $toUserId, $toUserId, $fromUserId)->fetch();

        if ($sameRequest && empty($sameRequest['approved'])) {
            if ($sameRequest['from_user_id'] === $fromUserId) {
                $sameRequest->update(['approved' => NULL, 'updated_at' => $this->getDateTime()]); //@todo frozen branch
            } else {
                $this->acceptFriendshipRequest($toUserId);
            }
            return TRUE;
        }

        if (!$sameRequest) {
            $insertData = [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'created_at' => $this->getDateTime(),
                'updated_at' => $this->getDateTime()
            ];

            $this->notificationModel->insertNotification(Notification::MESSAGE_NEW_FRIEND_REQUEST, $toUserId);
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
        $updated = $this->findBy(['from_user_id' => $userId, 'to_user_id' => $toUserId])->update($updateData);

        if ($updated && empty($updated['approved']) && $approve) {
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
        return $this->getTable()->where("from_user_id = ? AND to_user_id = ? OR from_user_id = ? AND to_user_id = ?", $fromUserId, $toUserId, $toUserId, $fromUserId)->delete();
    }
}