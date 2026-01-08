<?php
declare(strict_types=1);

use App\Components\ActivityForm\IActivityFormFactory;
use App\Components\ChallengeForm\IChallengeFormFactory;
use App\Model\ActivityLog;
use App\Model\Challenge;
use App\Model\ChallengeUser;
use App\Presenters\ChallengePresenter;
use Nette\Application\IPresenterFactory;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class ChallengeFlowTest extends BaseTestCase
{
    public function testCreateChallengeAndLogActivity(): void
    {
        $user = $this->loginDefaultUser();
        $userId = $user->getIdentity()->id;

        // Create presenter to attach components to
        $factory = $this->container->getByType(IPresenterFactory::class);
        $presenter = $factory->createPresenter('Challenge');
        
        /** @var IChallengeFormFactory $challengeFormFactory */
        $challengeFormFactory = $this->container->getByType(IChallengeFormFactory::class);
        $challengeControl = $challengeFormFactory->create($userId);
        
        // Attach component to presenter
        $challengeControl->setParent($presenter);
        
        $challengeForm = $challengeControl['challengeForm'];

        $challengeData = ArrayHash::from([
            'name' => 'Integration challenge',
            'description' => 'Created from integration test',
            'activity_id' => 1,
            'start_at' => '',
            'end_at' => '',
            'final_value' => 50,
            'users' => '',
        ]);

        $challengeControl->formSent($challengeForm, $challengeData);

        /** @var Challenge $challengeModel */
        $challengeModel = $this->container->getByType(Challenge::class);
        $createdChallenge = $challengeModel->findOneBy(['name' => $challengeData->name]);
        Assert::notSame(null, $createdChallenge);
        Assert::same($userId, (int) $createdChallenge->created_by);

        /** @var ChallengeUser $challengeUserModel */
        $challengeUserModel = $this->container->getByType(ChallengeUser::class);
        $creatorMembership = $challengeUserModel->findOneBy([
            'challenge_id' => $createdChallenge->id,
            'user_id' => $userId,
        ]);
        Assert::notSame(null, $creatorMembership);

        /** @var IActivityFormFactory $activityFormFactory */
        $activityFormFactory = $this->container->getByType(IActivityFormFactory::class);
        $activityControl = $activityFormFactory->create($userId, $createdChallenge->id);
        
        // Attach component to presenter
        $activityControl->setParent($presenter);
        
        $activityForm = $activityControl['activityForm'];

        $activityData = ArrayHash::from([
            'value' => 15,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $activityControl->formSent($activityForm, $activityData);

        /** @var ActivityLog $activityLogModel */
        $activityLogModel = $this->container->getByType(ActivityLog::class);
        $activityRecord = $activityLogModel->findBy([
            'user_id' => $userId,
            'activity_id' => $createdChallenge->activity_id,
        ])->order('id DESC')->fetch();

        Assert::notSame(null, $activityRecord);
        Assert::same($activityData->value, (int) $activityRecord->value);
    }
}

(new ChallengeFlowTest())->run();

