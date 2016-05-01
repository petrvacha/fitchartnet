<?php


namespace App\Model;


use Nette\Application\LinkGenerator;

class Notification extends BaseModel
{
    /** @const MESSAGE_NEW_CHALLENGE string */
    const MESSAGE_NEW_CHALLENGE = 'New challenge request';

    /** @const MESSAGE_NEW_FRIEND_REQUEST string */
    const MESSAGE_NEW_FRIEND_REQUEST = 'New friendship request';


    /** @var \App\Model\Role $user */
    protected $user;

    /** @var LinkGenerator $linkGenerator */
    protected $linkGenerator;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                LinkGenerator $linkGenerator)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param string $message
     * @param int $userId
     */
    public function insertNotification($message, $userId)
    {
        switch ($message) {
            case self::MESSAGE_NEW_CHALLENGE:
                $link = $this->linkGenerator->link('Challenge:default');
                break;
            case self::MESSAGE_NEW_FRIEND_REQUEST:
                $link = $this->linkGenerator->link('Friends:default');
                break;
            default:
                $link = NULL;
        }

        if ($link) {
            $insert = [
                'user_id' => $userId,
                'message' => $message,
                'link' => $link
            ];
            $this->insert($insert);
        }
    }

    /**
     * @return mixed
     */
    public function getNewNotifications()
    {
        $userId = $this->user->getIdentity()->id;
        return $this->findBy(['user_id' => $userId, 'seen' => FALSE])->fetchAll();
    }

    public function setSeenAll()
    {
        $userId = $this->user->getIdentity()->id;
        $this->findBy(['user_id' => $userId, 'seen' => FALSE])->update(['seen' => TRUE]);
    }

    /**
     * @param $id
     */
    public function setNotificationSeen($id)
    {
        $this->findBy(['id' => $id])->update(['seen' => TRUE]);
    }
}