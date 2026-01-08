<?php
declare(strict_types=1);

use App\Model\Activity;
use App\Model\ActivityLog;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class ActivityLogModelTest extends BaseTestCase
{
    public function testInsertAndFetchActivity(): void
    {
        $user = $this->loginDefaultUser();
        $userId = $user->getIdentity()->id;

        /** @var Activity $activityModel */
        $activityModel = $this->container->getByType(Activity::class);
        
        // Find or create a valid activity
        $activity = $activityModel->findOneBy(['active' => true]);
        if (!$activity) {
            $activity = $activityModel->insert([
                'log_type_id' => 1,
                'name' => 'test activity',
                'active' => 1,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]);
        }
        $activityId = $activity->id;
        Assert::notSame(null, $activityId);

        /** @var ActivityLog $activityLogModel */
        $activityLogModel = $this->container->getByType(ActivityLog::class);

        $record = $activityLogModel->insert([
            'user_id' => $userId,
            'activity_id' => $activityId,
            'value' => 25,
            'active' => 1,
            'created_at' => new \DateTime('-1 hour'),
            'updated_at' => new \DateTime('-1 hour'),
        ]);

        Assert::notSame(null, $record);

        $userActivities = $activityLogModel->getUserActivityList($userId);
        Assert::true(count($userActivities) >= 1);

        $prepared = $activityLogModel->getUserPreparedData($userId);
        Assert::true(isset($prepared['values'][$activityId]));

        $activityLogModel->deleteActivity($record->id, $userId);
        Assert::same(null, $activityLogModel->findRow($record->id));
    }
}

(new ActivityLogModelTest())->run();

