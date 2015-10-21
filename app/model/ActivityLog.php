<?php

namespace App\Model;


/**
 * ActivityLog Model
 */
class ActivityLog extends BaseModel
{
    /**
     * @param int $userId
     * @param int|NULL $activityId
     * @return mixed
     */
    public function getUserActities($userId, $activityId = NULL)
    {
        $conditions = ['user_id' => $userId, 'active' => TRUE];
        if ($activityId) {
            $conditions = array_merge($conditions,  ['activity_id' => $activityId]);
        }
        return $this->findBy($conditions)->order('updated_at DESC')->fetchAll();
    }

    /**
     * @param int $id
     * @param int $userId
     */
    public function deleteActivity($id, $userId)
    {
        $this->findBy(['id' => $id, 'user_id' => $userId])->delete();
    }
}