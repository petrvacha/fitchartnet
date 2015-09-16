<?php

namespace App\Presenters;


/**
 * Challenges presenter
 */
class ChallengesPresenter extends LoginBasePresenter
{
    /** @var \App\Model\ActivityLog */
    protected $activityLog;

    
    /**
     * @param \App\Model\ActivityLog $activityLog
     */
    public function __construct(\App\Model\ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    public function renderDefault()
    {
        $this->template->title = 'Stats';
    }

}
