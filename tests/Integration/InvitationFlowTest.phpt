<?php
declare(strict_types=1);

use App\Components\ChallengeForm\IChallengeFormFactory;
use App\Model\Challenge;
use App\Model\ChallengeUser;
use App\Model\Friend;
use App\Model\User as UserModel;
use Fitchart\Application\Utilities;
use Nette\Application\IPresenterFactory;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class InvitationFlowTest extends BaseTestCase
{
    public function testInvitationRegistersUserIntoChallenge(): void
    {
        $creator = $this->loginDefaultUser();
        $creatorId = $creator->getIdentity()->id;

        // Create presenter to attach components to
        $presenterFactory = $this->container->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter('Challenge');

        /** @var IChallengeFormFactory $challengeFormFactory */
        $challengeFormFactory = $this->container->getByType(IChallengeFormFactory::class);
        $challengeControl = $challengeFormFactory->create($creatorId);
        
        // Attach component to presenter
        $challengeControl->setParent($presenter);
        
        $challengeForm = $challengeControl['challengeForm'];

        $challengeData = ArrayHash::from([
            'name' => 'Invitation challenge',
            'description' => 'Invitation link flow',
            'activity_id' => 1,
            'start_at' => '',
            'end_at' => '',
            'final_value' => 20,
            'users' => '',
        ]);

        $challengeControl->formSent($challengeForm, $challengeData);

        /** @var Challenge $challengeModel */
        $challengeModel = $this->container->getByType(Challenge::class);
        $challenge = $challengeModel->findOneBy(['name' => $challengeData->name]);
        Assert::notSame(null, $challenge);

        $invitationHash = Utilities::generateInvitationHash((int) $challenge->id, $challenge->created_at);

        $this->container->getByType(\Nette\Security\User::class)->logout(true);
        $this->refreshContainer([
            'invitationChallenge' => (int) $challenge->id,
            'invitationHash' => $invitationHash,
        ]);

        /** @var UserModel $userModel */
        $userModel = $this->container->getByType(UserModel::class);
        $inviteeName = uniqid('invitee_', true);
        $invitee = ArrayHash::from([
            'username' => $inviteeName,
            'email' => $inviteeName . '@example.com',
            'password' => 'invitation123',
        ]);
        $userModel->add($invitee);
        $createdInvitee = $userModel->findOneBy(['username' => $invitee->username]);
        Assert::notSame(null, $createdInvitee);

        /** @var IPresenterFactory $presenterFactory */
        $presenterFactory = $this->container->getByType(IPresenterFactory::class);
        $loginPresenter = $presenterFactory->createPresenter('Login');
        $loginPresenter->autoCanonicalize = false;
        $signInControl = $loginPresenter->getComponent('signInForm');
        $signForm = $signInControl['signForm'];

        $loginValues = ArrayHash::from([
            'username' => $invitee->username,
            'password' => $invitee->password,
        ]);

        foreach ($signForm->onSuccess as $callback) {
            call_user_func($callback, $signForm, $loginValues);
        }

        /** @var ChallengeUser $challengeUserModel */
        $challengeUserModel = $this->container->getByType(ChallengeUser::class);
        $membership = $challengeUserModel->findOneBy([
            'challenge_id' => $challenge->id,
            'user_id' => $createdInvitee['id'],
        ]);
        Assert::notSame(null, $membership);

        /** @var Friend $friendModel */
        $friendModel = $this->container->getByType(Friend::class);
        Assert::notSame(null, $friendModel->areFriends($creatorId, $createdInvitee['id']));
    }
}

(new InvitationFlowTest())->run();

