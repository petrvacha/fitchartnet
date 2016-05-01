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
    const NUMBER_THOUSAND = 1000;


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
    public function __construct(Notification $notificationModel,
                                ActivityLog $activityLog,
                                Weight $weightModel)
    {
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

        if (empty($this->template->activities)) {
            $firstAttemp = date('Y-01-01 00:00:00');
        } else {
            end($this->template->activities);
            $firstAttemp= current($this->template->activities)['updated_at']->format('Y-m-01 00:00:00');
        }

        if (date("l") === "Sunday"){
            $endOfWeek = strtotime(date("Y-m-d 23:59:59"));
        } else {
            $endOfWeek = strtotime(date("Y-m-d 23:59:59", strtotime('this Sunday')));
        }
        $endOfMonth = strtotime(date('Y-m-t 23:59:59')) * self::NUMBER_THOUSAND;

        $this->template->dates = [
            'now' => strtotime(date("Y-m-d H:i:s")) * self::NUMBER_THOUSAND,
            'week' => [strtotime(date('Y-m-d 00:00:00', strtotime('monday this week'))) * self::NUMBER_THOUSAND, $endOfWeek * self::NUMBER_THOUSAND],
            'month' => [strtotime(date('Y-m-01 00:00:00')) * self::NUMBER_THOUSAND, $endOfMonth],
            'year' => [strtotime(date('Y-01-01 00:00:00')) * self::NUMBER_THOUSAND, strtotime('Dec 31') * self::NUMBER_THOUSAND],
            'all' => [strtotime($firstAttemp) * self::NUMBER_THOUSAND, $endOfMonth]
        ];
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
        $this->redirect('Homepage:default');
    }

    /**
     * @return \App\Components\ActivityForm
     */
    protected function createComponentActivityForm()
    {
        $control = $this->activityFormFactory->create($this->user->id, NULL);

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
