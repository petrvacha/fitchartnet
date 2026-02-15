<?php


namespace App\Model;

use Nette\Application\LinkGenerator;

class Notification extends BaseModel
{
    /** @const MESSAGE_NEW_CHALLENGE string */
    public const MESSAGE_NEW_CHALLENGE = 'New challenge request';

    /** @const MESSAGE_NEW_FRIEND_REQUEST string */
    public const MESSAGE_NEW_FRIEND_REQUEST = 'New friendship request';

    /** @const MESSAGE_NEW_GROUP_INVITATION string */
    public const MESSAGE_NEW_GROUP_INVITATION = 'New group invitation';


    /** @var \App\Model\Role $user */
    protected $user;

    /** @var LinkGenerator $linkGenerator */
    protected $linkGenerator;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(
        \Nette\Database\Context $context,
        \Nette\Security\User $user,
        LinkGenerator $linkGenerator
    ) {
        parent::__construct($context);
        $this->user = $user;
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param string $message
     * @param int $userId
     * @param string|null $customLink
     */
    public function insertNotification($message, $userId, $customLink = null)
    {
        if ($customLink) {
            $link = $customLink;
        } else {
            switch ($message) {
                case self::MESSAGE_NEW_CHALLENGE:
                    $link = $this->linkGenerator->link('Challenge:default');
                    break;
                case self::MESSAGE_NEW_FRIEND_REQUEST:
                    $link = $this->linkGenerator->link('Friends:default');
                    break;
                case self::MESSAGE_NEW_GROUP_INVITATION:
                    $link = $this->linkGenerator->link('Group:default');
                    break;
                default:
                    $link = null;
            }
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
        return $this->findBy(['user_id' => $userId, 'seen' => false])->fetchAll();
    }

    public function setSeenAll()
    {
        $userId = $this->user->getIdentity()->id;
        $this->findBy(['user_id' => $userId, 'seen' => false])->update(['seen' => true]);
    }

    /**
     * @param $id
     */
    public function setNotificationSeen($id)
    {
        $this->findBy(['id' => $id])->update(['seen' => true]);
    }
}
