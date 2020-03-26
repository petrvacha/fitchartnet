<?php

namespace App\Model;
use Fitchart\Application\ChallengeStatus;
use Fitchart\Application\SecurityException;
use Nette\Utils\DateTime;


/**
 * Challenge Model
 */
class Challenge extends BaseModel
{
    /** @const STATE_FINISHED int */
    const STATE_FINISHED = 0;

    /** @const STATE_NEW int */
    const STATE_NEW = 1;

    /** @const STATE_IN_PROGRESS int */
    const STATE_IN_PROGRESS = 2;

    /** @const STATE_CANCELED int */
    const STATE_CANCELED = 3;

    /** @const STATE_INACTIVE int */
    const STATE_INACTIVE = 4;

    /** @const TEXT_STATUS_PREPARED string */
    const TEXT_STATUS_PREPARED = 'Prepared';

    /** @const TEXT_STATUS_ACTIVE string */
    const TEXT_STATUS_ACTIVE = 'Active';

    /** @const TEXT_STATUS_GONE string */
    const TEXT_STATUS_GONE = 'Gone';


    /** @var \Nette\Security\User */
    protected $user;

    /** @var User */
    protected $userModel;

    /** @var ChallengeUser */
    protected $challengeUserModel;

    /** @var Notification */
    protected $notificationModel;


    /**
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param User $userModel
     * @param ChallengeUser $challengeUserModel
     * @param Notification $notificationModel
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                User $userModel,
                                ChallengeUser $challengeUserModel,
                                Notification $notificationModel)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->userModel = $userModel;
        $this->challengeUserModel = $challengeUserModel;
        $this->notificationModel = $notificationModel;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getChallengeData($id)
    {
        $data = $this->findOneBy(['id' => $id]);
        if ($data) {
            $data = $data->toArray();
            $users = $this->challengeUserModel->findBy(['challenge_id' => $id])->fetchAll();
            $data['users'] = [];
            foreach ($users as $user) {
                $data['users'][] = ['id' => $user->user_id, 'name' => $user->user->username];
            }
        }
        return $data;
    }

    /**
     * @param array $data
     */
    public function createNewChallenge($data)
    {
        $data['state'] = self::STATE_NEW;
        $data['created_by'] = $this->user->getIdentity()->id;
        $data['created_at'] = $data['updated_at'] = $this->getDateTime();

        if (empty($data['start_at'])) {
            $data['start_at'] = $this->getDateTime('Y-m-d 00:00:00');
        }

        if (empty($data['end_at'])) {
            $data['end_at'] = date('Y-m-t 23:59:59');
        } else {
            $d = DateTime::createFromFormat("Y/m/d", $data['end_at']);
            $data['end_at'] = $d->format('Y-m-d 23:59:59');
        }

        $users = $data['users'];
        unset($data['users']);
        unset($data['id']);
        $row = $this->insert($data);

        $this->addUsersToChallenge($row->id, $users);
    }

    /**
     * @param array $data
     */
    public function updateChallenge($data)
    {
        $id = $data['id'];
        unset($data['id']);

        $challenge = $this->findBy(['id' => $id]);
        $challengeData = $challenge->select('created_by')->fetch();

        if ($challengeData['created_by'] === $this->user->id || $this->user->getIdentity()->role <= Role::MODERATOR) {
            $data['updated_at'] = $this->getDateTime();

            if (empty($data['start_at'])) {
                $data['start_at'] = $this->getDateTime();
            }

            if (empty($data['end_at'])) {
                $data['end_at'] = date('Y/m/t 23:59');
            }

            $this->updateUsersInChallenge($id, $data['users']);
            unset($data['users']);
            $challenge->update($data);
        }
    }

    /**
     * @param int $challengeId
     * @param string $users
     */
    public function addUsersToChallenge($challengeId, $users)
    {
        foreach (array_unique(explode(',', $users)) as $userId) {
            if (is_numeric($userId) && $this->userModel->hasPermissionForUser($userId)) {
                $this->challengeUserModel->addNewUser($challengeId, $userId);
                $this->notificationModel->insertNotification(Notification::MESSAGE_NEW_CHALLENGE, $userId);
            }
        }
        $this->challengeUserModel->addNewUser($challengeId, $this->user->getIdentity()->id, TRUE);
    }

    /**
     * @param $challengeId
     * @param $users
     */
    private function updateUsersInChallenge($challengeId, $users)
    {
        $newUserIds = array_unique(explode(',', $users));
        $oldUsers = $this->challengeUserModel
            ->findBy(['challenge_id' => $challengeId])
            ->select('user_id')
            ->fetchAll();
        $oldUserIds = [];
        foreach ($oldUsers as $oldUser) {
            $oldUserIds[] = $oldUser['user_id'];
        };

        foreach ($newUserIds as $userId) {
            if (is_numeric($userId) && $this->userModel->hasPermissionForUser($userId) && !in_array($userId, $oldUserIds)) {
                $this->challengeUserModel->addNewUser($challengeId, $userId);
            }
        }

        foreach ($oldUserIds as $oldUserId) {
            if (!in_array($oldUserId, $newUserIds)) {
                $this->challengeUserModel->removeUser($challengeId, $oldUserId);
            }
        }
    }

    /**
     * @return array
     */
    public function getLastUserChallenge()
    {
        return $this->context->query("
            SELECT
                C.id
            FROM
                challenge C
            JOIN challenge_user CU ON
                CU.challenge_id = C.id
            WHERE
                CU.user_id = ?
            ORDER BY
                C.end_at DESC", $this->user->getIdentity()->id
        )->fetch();
    }

    /**
     * @return array
     */
    public function getUserChallenges()
    {
        return $this->context->query("
            SELECT
                C.id,
                C.name,
                C.description,
                C.created_by,
                IFNULL(SUM(AL.value),0)
                current_value,
                C.final_value,
                A.name activity_name,
                CU.active,
                C.end_at,
                LT.mark log_type_mark
            FROM
                challenge C
            JOIN
                activity A ON A.id = C.activity_id
            JOIN challenge_user CU ON
                CU.challenge_id = C.id
            LEFT JOIN activity_log AL ON
                AL.activity_id = C.activity_id AND
                C.start_at < AL.created_at AND
                C.end_at > AL.created_at AND
                AL.user_id = CU.user_id
            LEFT JOIN log_type LT ON
                LT.id = A.log_type_id
            WHERE
                CU.user_id = ?
            GROUP BY
                C.id
            ORDER BY
                C.end_at DESC", $this->user->getIdentity()->id
        )->fetchAll();
    }

    /**
     * @param $challengeId
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getCurrentUserPerformances($challengeId)
    {
        return $this->context->query("
            SELECT U.id, U.username, CU.color, SUM(AL.value) current_performance
            FROM activity_log AL
            JOIN user U ON U.id = AL.user_id
            JOIN challenge C ON C.activity_id = AL.activity_id AND C.end_at > AL.created_at AND C.start_at< AL.created_at
            JOIN challenge_user CU ON CU.user_id = U.id AND CU.challenge_id = C.id
            WHERE C.id = ?
            GROUP BY U.id
            ORDER BY current_performance DESC", $challengeId)->fetchAll();
    }

    /**
     * @todo rewrite
     * @param int $challengeId
     * @return array
     */
    public function getChallengeUsers($challengeId)
    {
        $challengeUsers = $this->challengeUserModel->findBy(['challenge_id' => $challengeId])->fetchAll();
        $users = [];
        foreach ($challengeUsers as $challengeUser) {
            $users[$challengeUser['user_id']] = $challengeUser->ref('user', 'user_id')->username;
        }
        return $users;
    }

    /**
     * Returns users' continuous and cumulative data
     * @param int $challengeId
     * @return array
     */
    public function getUsersPerformances($challengeId)
    {
        $returnData = [];
        $returnData['days'] = [];
        $returnData['normal'] = [];

        $data = $this->context->query("
            SELECT
                *
            FROM
                user_challenge_performance_view
            WHERE
                challenge_id = ?
            ORDER BY created_at", $challengeId)->fetchAll();

        $challenge = $this->context->query("
                SELECT
                    C.start_at,
                    C.end_at
                FROM
                    challenge C
                WHERE
                    C.id = ?", $challengeId)->fetchAll();

        $startDateTime = new \DateTime($challenge[0]['start_at']);
        $startDateTime->setTime(0,0,0);
        $endDateTime = $challenge[0]['end_at'];
        $endDateTime->setTime(23,59,59);

        for ($day = clone $startDateTime; $day <= $endDateTime; $day->add(new \DateInterval('P1D'))) {
            $returnData['days'][] = $day->format('d.m.Y');
        }

        foreach ($data as $record) {
            if (!isset($returnData['normal'][$record['username']])) {
                $returnData['normal'][$record['username']] = [];
                $returnData['normal'][$record['username']]['days'] = [];
                $returnData['normal'][$record['username']]['userId'] = $record['user_id'];
                $returnData['normal'][$record['username']]['color'] = $record['color'];
                $returnData['cumulative'][$record['username']] = [];
                $returnData['cumulative'][$record['username']]['days'] = [];
                $returnData['cumulative'][$record['username']]['userId'] = $record['user_id'];
                $returnData['cumulative'][$record['username']]['color'] = $record['color'];
                $returnData['cumulative'][$record['username']]['cumulativeSum'] = 0;
            }
            $returnData['normal'][$record['username']]['days'][$record['created_at']->format('d.m.Y')] = $record['value'];

            $newCumulativeValue = $returnData['cumulative'][$record['username']]['cumulativeSum'] + $record['value'];
            $returnData['cumulative'][$record['username']]['days'][$record['created_at']->format('d.m.Y')] = $newCumulativeValue;
            $returnData['cumulative'][$record['username']]['cumulativeSum'] = $newCumulativeValue;
        }

        $lastDay = null;
        foreach ($returnData['days'] as $day) {
            foreach ($returnData['normal'] as $username => $userData) {
                if (!isset($returnData['cumulative'][$username]['days'][$day])) {
                    $returnData['normal'][$username]['days'][$day] = 0;
                    if ($lastDay && isset($returnData['cumulative'][$username]['days'][$lastDay])) {
                        $returnData['cumulative'][$username]['days'][$day] = $returnData['cumulative'][$username]['days'][$lastDay];
                    } else {
                        $returnData['cumulative'][$username]['days'][$day] = 0;
                    }
                }
            }
            $lastDay = $day;
        }

        foreach ($returnData['normal'] as $username => $userData) {
            ksort($returnData['normal'][$username]['days']);
            ksort($returnData['cumulative'][$username]['days']);
            $returnData['normal'][$username]['daysNoIndex'] = array_values($returnData['normal'][$username]['days']);
            $returnData['cumulative'][$username]['daysNoIndex'] = array_values($returnData['cumulative'][$username]['days']);
        }

        return $returnData;
    }

    /**
     * @param \DateTime $endAt
     * @return int
     */
    public function getDaysLeft($endAt)
    {
        $today = new \DateTime();

        $dayLeft = $endAt->diff($today)->days;

        return $endAt > $today ? ++$dayLeft : 0;
    }

    /**
     * @param string $startAt
     * @param string $endAt
     * @return ChallengeStatus
     */
    public function getChallengeStatus($startAt, $endAt )
    {
        $now = new \DateTime();

        if ($now < $endAt) {
            if ($startAt < $now) {
                return new ChallengeStatus(self::TEXT_STATUS_ACTIVE);
            } else {
                return new ChallengeStatus(self::TEXT_STATUS_PREPARED);
            }
        } else {
            return new ChallengeStatus(self::TEXT_STATUS_GONE);
        }
    }
}