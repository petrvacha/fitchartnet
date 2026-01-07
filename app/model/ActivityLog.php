<?php


namespace App\Model;

use DateInterval;
use DateTime;

/**
 * ActivityLog Model
 */
class ActivityLog extends BaseModel
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function getUserActities($userId)
    {
        $conditions = ['user_id' => $userId, 'active' => true];
        return $this->findBy($conditions)->order('updated_at DESC')->fetchAll();
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getUserActivityList($userId)
    {
        $conditions = ['user_id' => $userId, 'active' => true];
        return $this->findBy($conditions)->group('activity_id')->fetchAll();
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserPreparedData($userId)
    {
        $data = $this
                    ->findBy(['user_id' => $userId, 'active' => true])
                    ->order('updated_at ASC')
                    ->fetchAll();

        $now = new DateTime();
        $thisWeekStart = clone $now;
        $thisWeekStart->modify('monday this week');
        $thisWeekEnd = clone $now;
        $thisWeekEnd->modify('sunday this week');
        $thisMonthStart = clone $now;
        $thisMonthStart->modify('first day of this month');
        $thisMonthEnd = clone $now;
        $thisMonthEnd->modify('last day of this month');
        $thisYearStart = clone $now;
        $thisYearStart->modify('first day of January this year');
        $thisYearEnd = clone $now;
        $thisYearEnd->modify('last day of December this year');

        $preparedData = [];
        $firstActivity = !empty($data) ? $data[key($data)]->updated_at : null;

        foreach ($data as $i => $item) {
            if (!isset($preparedData[$item->activity_id])) {
                $preparedData[$item->activity_id] = [
                    'name' => $item->activity->name,
                    'week' => [],
                    'month' => [],
                    'year' => [],
                    'all' => []
                ];
                for ($day = clone $thisWeekStart; $day <= $thisWeekEnd; $day->add(new DateInterval('P1D'))) {
                    $preparedData[$item->activity_id]['week'][$day->format('d.m.Y')] = 0;
                }
                for ($day = clone $thisMonthStart; $day <= $thisMonthEnd; $day->add(new DateInterval('P1D'))) {
                    $preparedData[$item->activity_id]['month'][$day->format('d.m.Y')] = 0;
                }
                for ($month = clone $thisYearStart; $month <= $thisYearEnd; $month->add(new DateInterval('P1M'))) {
                    $preparedData[$item->activity_id]['year'][$month->format('m.Y')] = 0;
                }
                if ($firstActivity) {
                    for ($year = clone $firstActivity; $year <= $thisYearEnd; $year->add(new DateInterval('P1Y'))) {
                        $preparedData[$item->activity_id]['all'][$year->format('Y')] = 0;
                    }
                }
            }

            $itemTime = $item->updated_at;
            $keyItemTime = $item->updated_at->format('d.m.Y');
            $keyMonthItemTime = $item->updated_at->format('m.Y');
            $keyYearItemTime = (string) $item->updated_at->format('Y');

            if ($itemTime >= $thisWeekStart) {
                $this->addValue($preparedData[$item->activity_id]['week'], $keyItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['month'], $keyItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyYearItemTime, $item->value);
            } elseif ($item->updated_at >= $thisMonthStart) {
                $this->addValue($preparedData[$item->activity_id]['month'], $keyItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyYearItemTime, $item->value);
            } elseif ($item->updated_at >= $thisYearStart) {
                $this->addValue($preparedData[$item->activity_id]['year'], $keyMonthItemTime, $item->value);
                $this->addValue($preparedData[$item->activity_id]['all'], $keyYearItemTime, $item->value);
            } else {
                $this->addValue($preparedData[$item->activity_id]['all'], $keyYearItemTime, $item->value);
            }
        }

        $indexArray = $preparedData;
        $valueArray = $preparedData;
        foreach ($preparedData as $activityId => $data) {
            foreach ($data as $interval => $values) {
                if ($interval !== 'name') {
                    unset($indexArray[$activityId][$interval]);
                    unset($valueArray[$activityId][$interval]);
                    foreach ($values as $index => $value) {
                        $indexArray[$activityId][$interval][] = $index;
                        $valueArray[$activityId][$interval][] = $value;
                    }
                }
            }
        }

        return ['indexes' => $indexArray, 'values' => $valueArray];
    }

    /**
     *
     * @param array $array
     * @param string $key
     * @param int $value
     */
    private function addValue(&$array, $key, $value)
    {
        if (isset($array[$key])) {
            $array[$key] += $value;
        } else {
            $array[$key] = $value;
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
