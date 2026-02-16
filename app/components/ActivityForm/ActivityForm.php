<?php

namespace App\Components;

use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * ActivityForm component
 */
class ActivityForm extends \Fitchart\Application\Control
{
    /** @var int */
    protected $userId;

    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Activity */
    protected $activityModel;

    /** @var \App\Model\Challenge */
    protected $challengeModel;

    /** @var \App\Model\ActivityLog */
    protected $activityLogModel;

    /** @var \App\Model\TelegramNotifier */
    protected $telegramNotifier;

    /** @var int */
    protected $challengeId;


    /**
     * @param int $userId
     * @param int $challengeId
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     * @param \App\Model\Challenge $challengeModel
     * @param \App\Model\ActivityLog $activityLogModel
     * @param \App\Model\TelegramNotifier $telegramNotifier
     */
    public function __construct(
        $userId,
        $challengeId,
        \App\Model\User $userModel,
        \App\Model\Activity $activityModel,
        \App\Model\Challenge $challengeModel,
        \App\Model\ActivityLog $activityLogModel,
        \App\Model\TelegramNotifier $telegramNotifier
    ) {
        $this->userId = $userId;
        $this->userModel = $userModel;
        $this->activityModel = $activityModel;
        $this->challengeModel = $challengeModel;
        $this->activityLogModel = $activityLogModel;
        $this->telegramNotifier = $telegramNotifier;
        $this->challengeId = $challengeId;
    }

    /**
     * @return Form
     */
    public function createComponentActivityForm()
    {
        $form = new Form();
        $form->addText('created_at', 'Time')
            ->setAttribute('placeholder', 'now')
            ->getControlPrototype()->addClass('datetimepicker');

        // $form['created_at']->getLabelPrototype()->style = 'float: left; width: 60px;';



        $countLabel = 'Count';
        $challenge = $this->challengeModel->findRow($this->challengeId);
        if ($challenge) {
            if ($challenge->activity->log_type->mark) {
                $countLabel .= ' [' . $challenge->activity->log_type->mark . ']';
            }
        } else {
            $form->addSelect('activity_id', 'Activity', $this->activityModel->getList());
        }


        $form->addText('value', $countLabel)
            ->setAttribute('placeholder', '0')
            ->setRequired('You forgot to fill important number.')
            ->addRule(Form::INTEGER, 'Wrong format. Input must be an integer.');
        //$form['value']->getLabelPrototype()->style = 'float: left; width: 65px;';

        $form->setDefaults($this->data);

        $form->addSubmit('submit', 'Add');

        $form->onSuccess[] = [$this, 'formSent'];

        $this->addBootstrapStyling($form);
        return $form;
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        $this->template->showActivitySelect = empty($this->challengeId);
        $this->template->time = date('Y/m/d H:00'); //@todo trunc minutes down
        $this->template->render();
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function formSent(Form $form, ArrayHash $values)
    {
        $values['user_id'] = $this->userId;

        if (!empty($this->challengeId)) {
            $challenge = $this->challengeModel->findRow($this->challengeId);
            $values['activity_id'] = $challenge['activity_id'];
        }
        if (empty($values['id'])) {
            if (empty($values['created_at'])) {
                $values['created_at'] = new \DateTime();
            }
            $values['updated_at'] = $values['created_at'];
            $this->activityLogModel->insert($values);
            try {
                $this->telegramNotifier->notifyActivityLog(
                    (int) $values['user_id'],
                    (int) $values['activity_id'],
                    (int) $values['value']
                );
            } catch (\Throwable $e) {
                \Tracy\Debugger::log(
                    sprintf(
                        'Telegram notifyActivityLog failed: user_id=%d, activity_id=%d – %s',
                        (int) $values['user_id'],
                        (int) $values['activity_id'],
                        $e->getMessage()
                    ),
                    \Tracy\ILogger::WARNING
                );
                \Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);
            }
        } else {
            $this->activityLogModel->update($values);
        }
    }
}
