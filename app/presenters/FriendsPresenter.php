<?php

namespace App\Presenters;


/**
 * Friends presenter
 */
class FriendsPresenter extends LoginBasePresenter
{
    /** @var \App\Model\ActivityLog */
    protected $activityLog;

    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Friend */
    protected $friendModel;

    /** @var \App\Model\FriendshipRequest */
    protected $friendshipRequestModel;

    
    /**
     * @param \App\Model\ActivityLog $activityLog
     * @param \App\Model\User $userModel
     * @param \App\Model\FriendshipRequest $friendshipModel
     * @param \App\Model\Friend $friendModel
     */
    public function __construct(\App\Model\ActivityLog $activityLog,
                                \App\Model\User $userModel,
                                \App\Model\FriendshipRequest $friendshipRequestModel,
                                \App\Model\Friend $friendModel
            )
    {
        $this->activityLog = $activityLog;
        $this->userModel = $userModel;
        $this->friendshipRequestModel = $friendshipRequestModel;
        $this->friendModel = $friendModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'Friends';
        $this->template->userList = $this->userModel->getUserList();
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
     * @param int $id
     */
    public function actionAcceptFriendship($id)
    {
        $friend = $this->friendshipRequestModel->acceptFriendshipRequest($id);
        if ($friend) {
            $this->flashMessage("$friend->username and you are friends now!", 'success');
        }
        $this->redirect('Friends:default');
    }

    /**
     * @param int $id
     */
    public function actionRemoveFriendshipRequest($id)
    {
        $removed = $this->friendshipRequestModel->removeFriendshipRequest($id, FALSE);
        if ($removed) {
            $this->flashMessage('Friendship request has been removed.', 'info');
        }
        $this->redirect('Friends:default');
    }

}
