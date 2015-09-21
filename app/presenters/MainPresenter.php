<?php

namespace App\Presenters;


/**
 * Main presenter
 */
class MainPresenter extends LoginBasePresenter
{
    /** @var \App\Model\ActivityLog */
    protected $activityLog;

    /** @var \App\Model\Weight */
    protected $weightModel;


    /** @var \App\Components\ActivityForm\IActivityFormFactory @inject */
    public $activityFormFactory;

    /** @var \App\Components\UserWeightForm\IUserWeightFormFactory @inject */
    public $userWeightFormFactory;

    
    /**
     * @param \App\Model\ActivityLog $activityLog
     * @param \App\Model\Weight $weightModel
     */
    public function __construct(\App\Model\ActivityLog $activityLog,
                                \App\Model\Weight $weightModel)
    {
        $this->activityLog = $activityLog;
        $this->weightModel = $weightModel;
    }

    public function renderDefault()
    {
        $this->template->title = 'News Feed';
    }

    public function renderAddWorkout()
    {
        $this->template->activities = $this->activityLog->getUserActities($this->user->id, 1);
        $this->template->title = 'Add workout';
    }

    public function renderWeight()
    {
        $this->template->title = 'Your weight';
        $this->template->weights = $this->weightModel->findBy(['user_id' => $this->user->id]);
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
            $this->flashMessage($awesomeShout[array_rand($awesomeShout)], parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Main:AddWorkout');
        };
        return $control;
    }



    /**
     * @return \App\Components\ActivityForm
     */
    protected function createComponentWeightForm()
    {
        $control = $this->userWeightFormFactory->create($this->user->id);

        $control->getComponent('userWeightForm')->onSuccess[] = function() {
            $this->flashMessage('New weight has been updated.', parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Main:Weight');
        };
        return $control;
    }

}
