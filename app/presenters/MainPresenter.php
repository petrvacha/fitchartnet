<?php

namespace App\Presenters;


/**
 * Main presenter
 */
class MainPresenter extends LoginBasePresenter
{
    /** @var \App\Model\ActivityLog */
    protected $activityLog;


    /** @var \App\Components\ActivityForm\IActivityFormFactory @inject */
    public $activityFormFactory;

    
    /**
     * @param \App\Model\ActivityLog $activityLog
     */
    public function __construct(\App\Model\ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    public function renderDefault()
    {
        $this->template->title = 'Overview';
        $this->template->activities = $this->activityLog->getUserActities($this->user->id, 1);
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:default');
    }

    /**
     * @return \App\Components\ActivityForm
     */
    protected function createComponentActivityForm()
    {
        $control = $this->activityFormFactory->create($this->user->id);
        
        $control->getComponent('activityForm')->onSuccess[] = function() {
            $awesomeShout = ['Good job!', 'Wooohooooo!', 'Not bad.', 'Awesome!'];
            $this->flashMessage($awesomeShout[array_rand($awesomeShout)], parent::MESSAGE_TYPE_INFO);
            $this->redirect('Main:default');
        };
        return $control;
    }

}
