<?php

namespace App\Presenters;


/**
 * Friends presenter
 */
class FriendsPresenter extends LoginBasePresenter
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
