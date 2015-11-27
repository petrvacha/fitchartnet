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
    public function getUserActities($userId)
    {
        $conditions = ['user_id' => $userId, 'active' => TRUE];
        return $this->findBy($conditions)->order('updated_at DESC')->fetchAll();
    }

    public function getUserActivityList($userId)
    {
        $conditions = ['user_id' => $userId, 'active' => TRUE];
        return $this->findBy($conditions)->group('activity_id')->fetchAll();
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserPreparedData($userId)
    {
        $data = $this
                    ->findBy(['user_id' => $userId, 'active' => TRUE])
                    ->order('updated_at ASC')
                    ->fetchAll();


        $thisWeekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        
        $thisMonthStart = date('Y-m-01 00:00:00');
        $thisYearStart = date('Y-01-01 00:00:00');

        $preparedData = [];
        
        foreach($data as $i => $item) {
            if (!isset($preparedData[$item->activity_id])) {
                $preparedData[$item->activity_id] = ['week' => [], 'month' => [], 'year' => [], 'all' => []];
            }

            $itemTime = $item->updated_at->format('Y-m-d H:i:s');
            
            if ($itemTime >= $thisWeekStart) {
                $keyItemTime = ($item->updated_at->modify('midnight')->getTimestamp() ) * 1000;
                $this->addValue($preparedData[$item->activity_id]['week'], $keyItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['month'], $keyItemTime, $item->value);
                $keyMonthItemTime = ($item->updated_at->modify('first day of this month')->getTimestamp()+ $item->updated_at->getOffset()) * 1000;
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyMonthItemTime, $item->value);

            } elseif ($item->updated_at >= $thisMonthStart) {
                $keyItemTime = ($item->updated_at->modify('midnight')->getTimestamp()) * 1000;
                $this->addValue($preparedData[$item->activity_id]['month'], $keyItemTime, $item->value);
                $keyMonthItemTime = ($item->updated_at->modify('first day of this month')->getTimestamp() + $item->updated_at->getOffset()) * 1000;
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyMonthItemTime, $item->value);

            } elseif ($item->updated_at >= $thisYearStart) {
                $keyMonthItemTime = ($item->updated_at->modify('first day of this month')->modify('midnight')->getTimestamp() + $item->updated_at->getOffset()) * 1000;
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyMonthItemTime, $item->value);

            } else {
                $keyMonthItemTime = ($item->updated_at->modify('first day of this month')->modify('midnight')->getTimestamp() + $item->updated_at->getOffset()) * 1000;
                $this->addValue($preparedData[$item->activity_id]['all'], $keyMonthItemTime, $item->value);
            }
        }

        $preparedArray = $preparedData;
        foreach ($preparedData as $activityId => $activity) {
            foreach ($activity as $interval => $data) {
                $first = TRUE;
                foreach ($data as $x => $y) {
                    if ($first) {
                        $preparedArray[$activityId][$interval] = [];
                        $first = FALSE;
                    }
                    $preparedArray[$activityId][$interval][] = [$x, $y];
                }
            }
        }
        //echo '<pre>';
        //var_dump($preparedArray);die;
        return $preparedArray;
    }

    /**
     *
     * @param array $array
     * @param string $key
     * @param int $value
     */
    private function addValue(&$array, $key, $value)
    {
        if (isset($array[(int) $key])) {
            $array[(int) $key] += $value;
        } else {
            $array[(int) $key] = $value;
        }
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