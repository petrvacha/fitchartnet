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
        $this->template->challenge = $this->challengeModel->findRow($id);
        $this->template->currentUserPerformances = $this->challengeModel->getCurrentUserPerformances($id);
        $this->template->currentTotalPerformance = 0 + array_reduce($this->template->currentUserPerformances, function($i, $obj) {
            return $i += $obj->current_performance;
        });

        $userPieData = [];
        foreach ($this->template->currentUserPerformances as $p) {
            $userPieData[] = ['label' => $p['username'], 'data' => $p['current_performance']];
        }
        $this->template->userPieData = $userPieData;
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
