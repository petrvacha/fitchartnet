<?php

namespace App\Presenters;


/**
 * Stats presenter
 */
class StatsPresenter extends LoginBasePresenter
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
