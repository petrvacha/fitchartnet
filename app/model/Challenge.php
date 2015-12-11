<?php

namespace App\Model;


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


    /** @var \Nette\Security\User */
    protected $user;

    /** @var User */
    protected $userModel;

    /** @var ChallengeUser */
    protected $challengeUserModel;


    /**
     * ChallengeUser constructor.
     * @param \Nette\Database\Context $context
     * @param \Nette\Security\User $user
     * @param User $userModel
     * @param ChallengeUser $challengeUserModel
     */
    public function __construct(\Nette\Database\Context $context,
                                \Nette\Security\User $user,
                                User $userModel,
                                ChallengeUser $challengeUserModel)
    {
        parent::__construct($context);
        $this->user = $user;
        $this->userModel = $userModel;
        $this->challengeUserModel = $challengeUserModel;
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
            $data['start_at'] = $this->getDateTime();
        }

        if (empty($data['end_at'])) {
            $data['end_at'] = date('Y/m/t 23:59');
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
        $data['updated_at'] = $this->getDateTime();

        if (empty($data['start_at'])) {
            $data['start_at'] = $this->getDateTime();
        }

        if (empty($data['end_at'])) {
            $data['end_at'] = date('Y/m/t 23:59');
        }

        $this->challengeUserModel->where(['challenge_id' => $data['id']])->delete();

        $this->addUsersToChallenge($data['id'], $data['users']);
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
            }
        }
        $this->challengeUserModel->addNewUser($challengeId, $this->user->getIdentity()->id, TRUE);
    }

    /**
     * @return array
     */
    public function getUserChallenges()
    {
        return $this->context->query("
            SELECT C.id, C.name, C.description, IFNULL(SUM(AL.value),0) current_value, C.final_value, A.name activity_name, CU.active
            FROM challenge C
            JOIN challenge_user CU ON CU.challenge_id = C.id
            LEFT JOIN activity_log AL ON AL.activity_id = C.activity_id AND C.start_at < AL.created_at AND C.end_at > AL.created_at
            JOIN activity A ON A.id = C.activity_id
            WHERE CU.user_id = ?
            GROUP BY C.id", $this->user->getIdentity()->id
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
}