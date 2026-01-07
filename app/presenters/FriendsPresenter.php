<?php

namespace App\Presenters;

use App\Model\ActivityLog;
use App\Model\Friend;
use App\Model\FriendshipRequest;
use App\Model\Notification;
use App\Model\User;

/**
 * Friends presenter
 */
class FriendsPresenter extends LoginBasePresenter
{
    /** @var ActivityLog */
    protected $activityLog;

    /** @var User */
    protected $userModel;

    /** @var Friend */
    protected $friendModel;

    /** @var FriendshipRequest */
    protected $friendshipRequestModel;

    /**
     * @param Notification $notificationModel
     * @param ActivityLog $activityLog
     * @param User $userModel
     * @param FriendshipRequest $friendshipRequestModel
     * @param Friend $friendModel
     */
    public function __construct(
        Notification $notificationModel,
        ActivityLog $activityLog,
        User $userModel,
        FriendshipRequest $friendshipRequestModel,
        Friend $friendModel
    ) {
        parent::__construct($notificationModel);
        $this->activityLog = $activityLog;
        $this->userModel = $userModel;
        $this->friendshipRequestModel = $friendshipRequestModel;
        $this->friendModel = $friendModel;
    }


    public function renderDefault()
    {
        $this->template->title = 'Friends';
        $this->template->userList = $this->userModel->getFreeUsers();
        $this->template->friendList = $this->userModel->getFriendList();
        $this->template->friendshipRequestList = $this->userModel->getFriendshipRequestList();
        $this->template->friendshipOfferList = $this->userModel->getFriendshipOfferList();
    }

    /**
     * @param string $subject
     */
    public function actionSearchUser($subject)
    {
        $this->template->users = $this->userModel->getUserList($subject);
        $this->redirect('Friends:default');
    }

    /**
     * @param int $id
     */
    public function actionRemoveFriend($id)
    {
        $friend = $this->friendModel->removeFriend($id);
        $this->friendshipRequestModel->removeFriendshipRequest($id);
        $this->friendshipRequestModel->removeFriendshipRequest($this->user->id);
        if ($friend) {
            $this->flashMessage("$friend and you are no longer friends :(", 'info');
        }
        $this->redirect('Friends:default');
    }

    /**
     * @param int $id
     */
    public function actionFriendshipRequest($id)
    {
        $friendshipRequestSent = $this->friendshipRequestModel->addFriendshipRequest($id);
        if ($friendshipRequestSent) {
            $this->flashMessage('Your friendship request has been sent.', 'info');
        }
        $this->redirect('Friends:default');
    }

    /**
     * @param $id
     * @param bool $approve
     * @throws \Nette\Application\AbortException
     */
    public function actionAcceptFriendship($id, $approve = true)
    {
        $friend = $this->friendshipRequestModel->acceptFriendshipRequest($id, $approve);
        if ($friend && $approve) {
            $this->flashMessage("$friend->username and you are friends now!", 'success');
        }
        $this->redirect('Friends:default');
    }

    /**
     * @param int $id
     */
    public function actionRemoveFriendshipRequest($id)
    {
        $removed = $this->friendshipRequestModel->removeFriendshipRequest($id);
        if ($removed) {
            $this->flashMessage('Friendship request has been removed.', 'info');
        }
        $this->redirect('Friends:default');
    }
}
