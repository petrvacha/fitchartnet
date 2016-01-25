<?php

namespace App\Model;


/**
 * ChallengeUser Model
 */
class ChallengeUser extends BaseModel
{
    /** @const COLOR_BLACK string */
    const COLOR_BLACK = '#000000';

    /** @const COLOR_WHITE string */
    const COLOR_WHITE = '#FFFFFF';

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
                    'color' => $this->generateColor($challengeId),
                    'active' => $active
                ]
            );
        }
    }

    /**
     * @param int $challengeId
     * @return string
     */
    private function generateColor($challengeId)
    {
        $hexArray = ['00', '33', '66', '99', 'CC', 'FF'];
        $users = $this->findBy(['challenge_id' => $challengeId])->fetchAll();

        $find = FALSE;
        while ($find === FALSE) {
            $r = array_rand($hexArray, 3);
            $generatedColor = '#' . $hexArray[$r[0]] . $hexArray[$r[1]] . $hexArray[$r[2]];

            if (!empty($users)) {
                foreach ($users as $user) {
                    if ($user['color'] !== $generatedColor &&
                        $generatedColor !== self::COLOR_BLACK &&
                        $generatedColor !== self::COLOR_WHITE) {

                        $find = TRUE;
                    }
                }

            } else {
                if ($generatedColor !== self::COLOR_BLACK &&
                    $generatedColor !== self::COLOR_WHITE) {

                    $find = TRUE;
                }
            }
        }

        return $generatedColor;
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