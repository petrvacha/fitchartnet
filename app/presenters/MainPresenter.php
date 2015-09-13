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
        
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:default');
    }
    
    /**
     * @param int $id activity_id
     */
    public function actionList($id)
    {
        $this->template->activities = $this->activityLog->getUserActities($this->user->id, $id);
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
