<?php

namespace App\Presenters;

use App\Model\ActivityLog;
use App\Model\Notification;
use App\Model\Weight;

/**
 * Main presenter
 */
class MainPresenter extends LoginBasePresenter
{
    /** @const NUMBER_THOUSAND int */
    public const NUMBER_THOUSAND = 1000;


    /** @var \App\Model\ActivityLog */
    protected $activityLog;

    /** @var \App\Model\Weight */
    protected $weightModel;


    /** @var \App\Components\ActivityForm\IActivityFormFactory @inject */
    public $activityFormFactory;

    /** @var \App\Components\UserWeightForm\IUserWeightFormFactory @inject */
    public $userWeightFormFactory;


    /**
     * @param Notification $notificationModel
     * @param ActivityLog $activityLog
     * @param Weight $weightModel
     */
    public function __construct(
        Notification $notificationModel,
        ActivityLog $activityLog,
        Weight $weightModel
    ) {
        parent::__construct($notificationModel);
        $this->activityLog = $activityLog;
        $this->weightModel = $weightModel;
    }

    public function renderAddWorkout()
    {
        $this->template->title = 'Add workout';
        $this->template->activities = $this->activityLog->getUserActities($this->user->id);
        $this->template->activityList = $this->activityLog->getUserActivityList($this->user->id);
        $this->template->chartData = $this->activityLog->getUserPreparedData($this->user->id);
        $this->template->firstActivityId = !empty($this->template->chartData['indexes']) ? key($this->template->chartData['indexes']) : null;
    }

    /**
     * @param int $id
     */
    public function actionDeleteWorknout($id)
    {
        $this->activityLog->deleteActivity($id, $this->user->id);
        $this->flashMessage('Worknout has been deleted.', self::MESSAGE_TYPE_INFO);
        $this->redirect('Main:addWorkout');
    }

    public function renderWeight()
    {
        $this->template->title = 'Your weight';
        $this->template->weights = $this->weightModel->getUserWeights($this->user->id);
    }

    /**
     * @param int $id
     */
    public function actionDeleteWeight($id)
    {
        $this->weightModel->deleteWeight($id, $this->user->id);
        $this->flashMessage('Weight record has been deleted.', self::MESSAGE_TYPE_INFO);
        $this->redirect('Main:weight');
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Homepage:launch');
    }

    /**
     * @return \App\Components\ActivityForm
     */
    protected function createComponentActivityForm()
    {
        $control = $this->activityFormFactory->create($this->user->id, null);

        $control->getComponent('activityForm')->onSuccess[] = function () {
            $awesomeShout = ['Good job!', 'Wooohooooo!', 'Not bad.', 'Awesome!'];
            $this->flashMessage($awesomeShout[array_rand($awesomeShout)], parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Main:AddWorkout');
        };
        return $control;
    }

    /**
     * @return \App\Components\UserWeightForm
     */
    protected function createComponentWeightForm()
    {
        $control = $this->userWeightFormFactory->create($this->user->id);

        $control->getComponent('userWeightForm')->onSuccess[] = function () {
            $this->flashMessage('New weight has been updated.', parent::MESSAGE_TYPE_SUCCESS);
            $this->redirect('Main:Weight');
        };
        return $control;
    }
}
