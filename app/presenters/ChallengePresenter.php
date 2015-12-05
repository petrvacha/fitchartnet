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

    }

    /**
     * @param $challengeId
     */
    public function actionJoin($challengeId)
    {
        $this->challengeUserModel->attend($challengeId);
        $this->redirect('Challenge:');
    }

    /**
     * @param $challengeId
     */
    public function actionLeave($challengeId)
    {
        $this->challengeUserModel->attend($challengeId, FALSE);
        $this->redirect('Challenge:detail', $challengeId);
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

}
