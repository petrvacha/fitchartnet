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
     * @param int $userId
     * @param bool $active
     */
    public function addNewUser($challengeId, $userId, $active = FALSE)
    {
        if (!$this->findBy(['challenge_id' => $challengeId, 'user_id' => $userId])->fetch()) {
            $this->insert(
                [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'invited_by' => $this->user->getIdentity()->id,
                    'invited_at' => $this->getDateTime(),
                    'color' => '#' . substr(md5(rand()), 0, 6),
                    'active' => $active
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
        $this->findBy(['challenge_id' => $challengeId, 'user_id' => $userId])->delete();
    }

    /**
     * @param $challengeId
     * @param bool $active
     */
    public function attend($challengeId, $active = TRUE)
    {
        $this->findBy(['challenge_id' => $challengeId, 'user_id' => $this->user->getIdentity()->id])
            ->update(['active' => $active]);
    }
}