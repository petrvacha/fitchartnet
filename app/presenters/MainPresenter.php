<?php

namespace App\Presenters;


/**
 * Main presenter
 */
class MainPresenter extends LoginBasePresenter
{
    /** @var \App\Model\ActivityLog */
    protected $activityLog;


    /** @var \IActivityFormFactory @inject */
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
     * @return Form
     */
    protected function createComponentActivityForm()
    {
        $form = $this->activityFormFactory->create();

        $form->onSuccess[] = function () {
            $this->redirect('Main:default');
        };
        return $form;
    }

}
