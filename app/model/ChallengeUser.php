<?php

namespace App\Model;


/**
 * ChallengeUser Model
 */
class ChallengeUser extends BaseModel
{
    /** @var \Nette\Security\User */
    protected $user;


    /**
     * ChallengeUser constructor.
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
     * @param int $challengeId
     * @param int
     * @todo exception for duplication
     */
    public function addNewUser($challengeId, $userId)
    {
        if (!$this->findBy(['challenge_id' => $challengeId, 'user_id' => $userId])->fetch()) {
            $this->insert(
                [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'add_by' => $this->user->getIdentity()->id,
                    'add_at' => $this->getDateTime()
                ]
            );
        }
    }

    /**
     * @param int $challengeId
     * @param int $userId
     */
    public function removeUser($challengeId, $userId)
    {
        $this->findBy(['challenge_id' => $challengeId, 'user_ud' => $userId])->delete();
    }
}