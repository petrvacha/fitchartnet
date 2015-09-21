<?php

namespace App\Model;


/**
 * ActivityLog Model
 */
class ActivityLog extends BaseModel
{
    /**
     * @param int $userId
     * @param int $activityId
     * @return mixed
     */
    public function getUserActities($userId, $activityId)
    {
        return $this->findBy(['user_id' => $userId, 'activity_id' => $activityId, 'active' => TRUE])->order('updated_at DESC')->fetchAll();
    }
}