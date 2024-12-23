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
    public function addNewUser($challengeId, $userId, $active = FALSE, $invitedByUserId = NULL)
    {
        if (!$invitedByUserId) {
            $invitedByUserId = $this->user->getIdentity()->id;
        }

        if (!$this->findBy(['challenge_id' => $challengeId, 'user_id' => $userId])->fetch()) {
            $this->insert(
                [
                    'challenge_id' => $challengeId,
                    'user_id' => $userId,
                    'invited_by' => $invitedByUserId,
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
    public function generateColor($challengeId)
    {
        $colors = [
            '0000FF',
            'A52A2A',
            '6495ED',
            '008000',
            '006400',
            '696969',
            '2F4F4F',
            '800000',
            '351849',
            'FF8C00',
            '00FFFF',
            'FF7F50',
            '2B94E4',
            '008000',
            'FF69B4',
            'FFD700',
            '8FBC8F',
            'FF00FF',
            '191970',
            'FFA500',
            'AAA000',
            '000080',
            '008080',
            'E6E6FA',
            'F0FFF0',
            'FFFF00',
            '9ACD32',
            '3CB371',
            '000080'
        ];

        $users = $this->findBy(['challenge_id' => $challengeId])->fetchAll();

        $find = FALSE;
        while ($find === FALSE) {
            $r = array_rand($colors);
            $generatedColor = '#' . $colors[$r];

            if (!empty($users)) {
                $find = TRUE;
                foreach ($users as $user) {
                    if ($user['color'] === $generatedColor) {
                        $find = FALSE;
                        break;
                    }
                }

            } else {
                $find = TRUE;
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