<?php

namespace App\Presenters;
use App\Model\Challenge;
use App\Model\ActivityLog;


/**
 * Challenge presenter
 */
class ChallengePresenter extends LoginBasePresenter
{
    /** @var ActivityLog */
    protected $activityLog;

    /** @var Challenge */
    protected $challengeModel;


    /** @var \App\Components\ChallengeForm\IChallengeFormFactory @inject */
    public $challengeFormFactory;

    
    /**
     * @param ActivityLog $activityLog
     * @param Challenge $challengeModel
     */
    public function __construct(ActivityLog $activityLog,
                                Challenge $challengeModel)
    {
        $this->activityLog = $activityLog;
        $this->challengeModel = $challengeModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'Stats';
        $this->template->challenges = $this->challengeModel->getUserChallenges();
    }

    public function renderChallenge($id)
    {

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
