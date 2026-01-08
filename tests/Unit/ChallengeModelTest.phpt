<?php
declare(strict_types=1);

use App\Model\Challenge;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class ChallengeModelTest extends BaseTestCase
{
    public function testCreateNewChallengeSetsDefaults(): void
    {
        $user = $this->loginDefaultUser();
        $userId = $user->getIdentity()->id;

        /** @var Challenge $challengeModel */
        $challengeModel = $this->container->getByType(Challenge::class);

        $payload = ArrayHash::from([
            'name' => 'Unit challenge',
            'description' => 'Unit test description',
            'activity_id' => 1,
            'start_at' => '',
            'end_at' => '',
            'final_value' => 100,
            'users' => '',
        ]);

        $challengeModel->createNewChallenge($payload);

        $created = $challengeModel->findOneBy(['name' => $payload->name]);
        Assert::notSame(null, $created);
        Assert::same($userId, (int) $created->created_by);
        Assert::same(Challenge::STATE_NEW, (int) $created->state);
        Assert::true($created->start_at instanceof \DateTimeInterface);
        Assert::true($created->end_at instanceof \DateTimeInterface);
    }
}

(new ChallengeModelTest())->run();

