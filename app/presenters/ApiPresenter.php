<?php

namespace App\Presenters;

use Fitchart\Application\Utilities;

/**
 * Api presenter
 */
class ApiPresenter extends \Nette\Application\UI\Presenter
{
    /** @const MESSAGE_TYPE_INFO string */
    public const STATUS_OK = 'ok';

    /** @const MESSAGE_TYPE_SUCCESS string */
    public const STATUS_ERROR = 'error';


    protected static $ACTION_TYPES = ['insert'];
    
    
    /** @var \App\Model\User */
    protected $userModel;

    /** @var \App\Model\Activity */
    protected $activityModel;

    /** @var \App\Model\ActivityLog */
    protected $activityLogModel;


    /**
     * @param \App\Model\User $userModel
     * @param \App\Model\Activity $activityModel
     * @param \App\Model\ActivityLog $activityLogModel
     */
    public function __construct(
        \App\Model\User $userModel,
        \App\Model\Activity $activityModel,
        \App\Model\ActivityLog $activityLogModel
    ) {
        $this->userModel = $userModel;
        $this->activityModel = $activityModel;
        $this->activityLogModel = $activityLogModel;
    }

    public function renderDocumentation()
    {
        $this->template->title = 'API Documentation';
    }

    /**
     * @param string $apiVersion
     */
    public function actionTest($apiVersion = null)
    {
        if ($apiVersion && !method_exists($this, 'processVersion' . str_replace('.', '', $apiVersion))) {
            $this->sendJson(['status' => self::STATUS_ERROR]);
        }
        $this->sendJson(['status' => self::STATUS_OK]);
    }
    
    /**
     * @param string|NULL $apiVersion
     * @param string|NULL $apiToken
     * @param string|NULL $actionType
     * @param string|NULL $value
     * @param string|NULL $datetime
     */
    public function actionProcess($apiVersion = null, $apiToken = null, $actionType = null, $activityId = null, $value = null, $datetime = null)
    {
        $apiVersionMethod = 'processVersion' . str_replace('.', '', $apiVersion);
        $userModel = $this->userModel;
        
        if (!$apiVersion || strlen($apiVersion) > 4 || !method_exists($this, $apiVersionMethod) ||
             !$apiToken || strlen($apiToken) !== $userModel::API_TOKEN_LENGTH || !ctype_alnum($apiToken) ||
             !$actionType || !in_array($actionType, self::$ACTION_TYPES) ||
             !$activityId || !is_numeric($activityId) ||
             !$value || !is_numeric($value) || strlen($value) > 5 ||
             ($datetime && !Utilities::verifyDate($datetime))) {
            $this->sendJson(['status' => self::STATUS_ERROR]);
        }

        $this->{$apiVersionMethod}($apiToken, $actionType, $activityId, $value, $datetime);
    }

    /**
     * @param string $apiToken
     * @param string $actionType
     * @param string $activityId
     * @param string $value
     * @param string|NULL $datetime
     */
    public function processVersion01($apiToken, $actionType, $activityId, $value, $datetime = null)
    {
        $user = $this->userModel->findBy(['api_token' => $apiToken])->fetch();
        $activity = $this->activityModel->findRow($activityId);

        if ($user && $activity) {
            try {
                $values = ['user_id' => $user->id, 'activity_id' => (int) $activityId, 'value' => $value];
                $values['created_at'] = $values['updated_at'] = $datetime ?: new \DateTime();
                $this->activityLogModel->{$actionType}($values);
            } catch (\Exception $e) {
                $this->sendJson(['status' => self::STATUS_ERROR]);
            }
            $this->sendJson(['status' => self::STATUS_OK]);
        } else {
            $this->sendJson(['status' => self::STATUS_ERROR]);
        }
    }
}
