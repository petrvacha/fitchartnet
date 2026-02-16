<?php

namespace App\Model;

/**
 * Sends activity log notifications to Telegram groups.
 */
class TelegramNotifier extends BaseModel
{
    /** @var string */
    protected $tableName = 'user';

    /** @var Group */
    protected $groupModel;

    /** @var Challenge */
    protected $challengeModel;

    /** @var ChallengeUser */
    protected $challengeUserModel;

    /** @var User */
    protected $userModel;

    /** @var string */
    private static $baseUrl = 'https://fitchart.net';

    /**
     * @param \Nette\Database\Context $context
     * @param Group $groupModel
     * @param Challenge $challengeModel
     * @param ChallengeUser $challengeUserModel
     * @param User $userModel
     */
    public function __construct(
        \Nette\Database\Context $context,
        Group $groupModel,
        Challenge $challengeModel,
        ChallengeUser $challengeUserModel,
        User $userModel
    ) {
        parent::__construct($context);
        $this->groupModel = $groupModel;
        $this->challengeModel = $challengeModel;
        $this->challengeUserModel = $challengeUserModel;
        $this->userModel = $userModel;
    }

    /**
     * Notify Telegram groups when user adds an activity log and participates in a challenge of the same type.
     *
     * @param int $userId
     * @param int $activityId
     * @param int $value
     */
    public function notifyActivityLog($userId, $activityId, $value)
    {
        $user = $this->userModel->findRow($userId);
        if (!$user) {
            return;
        }

        $challengeUserRows = $this->context->table('challenge_user')
            ->where('user_id', $userId)
            ->where('active != ?', 0)
            ->fetchAll();

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $challenges = [];
        foreach ($challengeUserRows as $cu) {
            $challenge = $this->challengeModel->findRow($cu->challenge_id);
            if (!$challenge || (int) $challenge->activity_id !== (int) $activityId) {
                continue;
            }
            $endAt = $challenge->end_at instanceof \DateTimeInterface
                ? $challenge->end_at
                : new \DateTime($challenge->end_at);
            if ($endAt <= $today) {
                continue;
            }
            $challenges[] = $challenge;
        }

        if (empty($challenges)) {
            return;
        }

        $groups = $this->groupModel->getGroupsWithTelegramForUser($userId);

        if (empty($groups)) {
            return;
        }

        foreach ($challenges as $challenge) {
            if (!isset($challenge->activity->log_type->mark)) {
                continue;
            }
            $mark = $challenge->activity->log_type->mark ?: '';
            $orderLabel = $this->getOrderLabel($challenge->id, $userId);
            $changeLabel = $this->getChangeLabel($challenge->id, $userId, $value);
            $text = '🏋️ ' . $challenge->name . ': ' . $user->username . ' +' . $value . ' ' . $mark . ' ' . $changeLabel . ' ' . $orderLabel
                . "\n🔗️ " . self::$baseUrl . '/challenge/detail/' . $challenge->id;

            foreach ($groups as $group) {
                $this->sendMessage($group->bot_token, $group->telegram_group_id, $text);
            }
        }
    }
    /**
     * @param int $challengeId
     * @param int $userId
     * @param int $value
     * @return string If user stayed in the same position, return ➡️,
     * if user moved up, return ⬆️, if user jumped over multiple positions, return ⏫
     */
    private function getChangeLabel($challengeId, $userId, $value)
    {
        $performances = $this->challengeModel->getCurrentUserPerformances($challengeId);

        $position = 1;
        $userPerformance = 0;
        foreach ($performances as $row) {
            if ((int) $row->id === (int) $userId) {
                $userPerformance = (int) $row->current_performance;
                break;
            }
            $position++;
        }

        $oldPerformance = $userPerformance - $value;
        $oldPosition = 1;
        foreach ($performances as $row) {
            if ((int) $row->id === (int) $userId) {
                continue;
            }
            if ((int) $row->current_performance > $oldPerformance) {
                $oldPosition++;
            }
        }

        $diff = $oldPosition - $position;
        if ($diff <= 0) {
            return '➡️';
        }
        if ($diff === 1) {
            return '⬆️';
        }
        return '⏫';
    }


    /**
     * @param int $challengeId
     * @param int $userId
     * @return string Emoji or order number for ranking (1st 🥇, 2nd 🥈, 3rd 🥉, 4th 🥔, 5+ number)
     */
    private function getOrderLabel($challengeId, $userId)
    {
        $performances = $this->challengeModel->getCurrentUserPerformances($challengeId);
        $position = 1;
        foreach ($performances as $row) {
            if ((int) $row->id === (int) $userId) {
                break;
            }
            $position++;
        }
        if ($position === 1) {
            return '🥇';
        }
        if ($position === 2) {
            return '🥈';
        }
        if ($position === 3) {
            return '🥉';
        }
        if ($position === 4) {
            return '🥔';
        }
        return $position . '.';
    }

    /**
     * @param string $botToken
     * @param string $chatId
     * @param string $text
     */
    private function sendMessage($botToken, $chatId, $text)
    {
        $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($payload),
                'timeout' => 5,
            ],
        ];
        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }
}
