<?php

namespace App\Presenters;
use App\Model\Challenge;
use App\Model\ActivityLog;
use App\Model\ChallengeUser;


/**
 * Challenge presenter
 */
class ChallengePresenter extends LoginBasePresenter
{
    /** @var ActivityLog */
    protected $activityLog;

    /** @var Challenge */
    protected $challengeModel;

    /** @var ChallengeUser */
    protected $challengeUserModel;


    /** @var \App\Components\ChallengeForm\IChallengeFormFactory @inject */
    public $challengeFormFactory;

    /** @var \App\Components\ActivityForm\IActivityFormFactory @inject */
    public $activityFormFactory;


    /**
     * @param ActivityLog $activityLog
     * @param Challenge $challengeModel
     * @param ChallengeUser $challengeUserModel
     */
    public function __construct(ActivityLog $activityLog,
                                Challenge $challengeModel,
                                ChallengeUser $challengeUserModel)
    {
        $this->activityLog = $activityLog;
        $this->challengeUserModel = $challengeUserModel;
        $this->challengeModel = $challengeModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'Stats';
        $this->template->challenges = $this->challengeModel->getUserChallenges();
    }

    /**
     * @param $id
     */
    public function renderDetail($id)
    {
        $users = $this->challengeModel->getChallengeUsers($id);
        $this->template->challenge = $challenge = $this->challengeModel->findRow($id);
        $this->template->usersPerformances = $this->challengeModel->getUsersPerformances($id, $users);

        $this->template->currentUserPerformances = $this->challengeModel->getCurrentUserPerformances($id);


        $users = [];
        $usersToday = [];
        foreach ($this->template->currentUserPerformances as $user) {
            $users[] = $user['username'];
            $usersToday[$user['username']] = 0;
        }

        $today = new \DateTime;
        $todayStartString = $today->format("Y-m-d 00:00:00");
        $todayEndString = $today->format("Y-m-d 23:59:59");

        foreach($this->template->usersPerformances['normal'] as $record) {
            if ($todayStartString <= $record['time'] && $todayEndString > $record['time']) {
                foreach ($users as $user) {
                    $usersToday[$user] += $record[$user];
                }
            }
        }
        $this->template->usersToday = $usersToday;

        $this->template->currentTotalPerformance = 0 + array_reduce($this->template->currentUserPerformances, function($i, $obj) {
            return $i += $obj->current_performance;
        });

        $userPieData = [];
        $this->template->activeUsers = [];

        $daysRemaining = $this->challengeModel->getDaysLeft($challenge['end_at']);
        $this->template->daysRemaining = $daysRemaining;

        $this->template->usersColors = [];
        foreach ($this->template->currentUserPerformances as $i => $p) {
            $userPieData[] = ['label' => $p['username'], 'data' => $p['current_performance'], 'color' => $p['color']];
            if ($p['current_performance']) {
                $this->template->activeUsers[] = $p['username'];
                $this->template->usersColors[] = $p['color'];
            }


            $diff = $challenge['final_value'] - $p['current_performance'] + $usersToday[$p['username']];

            if ($diff > 0 && $daysRemaining) {
                $this->template->currentUserPerformances[$i]['average_minimum'] = ceil($diff / $daysRemaining);
            } else {
                $this->template->currentUserPerformances[$i]['average_minimum'] = '-';
            }
            $this->template->currentUserPerformances[$i]['percentage'] = ceil($p['current_performance']*100/$challenge['final_value']);
        }

        $this->template->users = $users;
        $this->template->userPieData = $userPieData;
        $tomorrow = new \DateTime('tomorrow');
        $this->template->tomorrow = $tomorrow->format('Y-m-d H:i:s');
        $this->template->challengeStatus = $this->challengeModel->getChallengeStatus($challenge['start_at'], $challenge['end_at']);

        $this->template->challengeDays = $this->template->challengeStatus !== Challenge::TEXT_STATUS_GONE ? $challenge['start_at']->diff($challenge['end_at'])->days + 1 : 0;
    }

    /**
     * @param $challengeId
     */
    public function actionJoin($challengeId)
    {
        $this->challengeUserModel->attend($challengeId);
        $this->redirect('Challenge:detail', $challengeId);
    }

    /**
     * @param $challengeId
     */
    public function actionLeave($challengeId)
    {
        $this->challengeUserModel->attend($challengeId, FALSE);
        $this->redirect('Challenge:');
    }

    /**
     * @return \App\Components\ChallengeForm
     */
    protected function createComponentChallengeForm()
    {
        $control = $this->challengeFormFactory->create($this->user->id);

        $control->getComponent('challengeForm')->onSuccess[] = function() {
            $this->flashMessage('New challenge has been created.', parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Challenge:');
        };
        return $control;
    }

    /**
     * @return \App\Components\ActivityForm
     */
    protected function createComponentActivityForm()
    {
        $control = $this->activityFormFactory->create($this->user->id);

        $control->getComponent('activityForm')->onSuccess[] = function() {
            $awesomeShout = ['Good job!', 'Wooohooooo!', 'Not bad.', 'Awesome!'];
            $this->flashMessage($awesomeShout[array_rand($awesomeShout)], parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Challenge:detail', $this->getParameter('id'));
        };
        return $control;
    }

}
