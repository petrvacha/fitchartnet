<?php
declare(strict_types=1);

use App\Model\User as UserModel;
use Nette\Application\Responses\RedirectResponse;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class AuthFlowTest extends BaseTestCase
{
    public function testRegistrationLoginAndLogout(): void
    {
        /** @var UserModel $userModel */
        $userModel = $this->container->getByType(UserModel::class);

        $unique = uniqid('user_', true);
        $payload = ArrayHash::from([
            'username' => $unique,
            'email' => $unique . '@example.com',
            'password' => 'secret123',
        ]);

        $userModel->add($payload);
        $created = $userModel->findOneBy(['username' => $payload->username]);
        Assert::notSame(null, $created);
        $token = $created['token'];

        $response = $this->runPresenter('Registration', 'check', ['hash' => $token]);
        Assert::type(RedirectResponse::class, $response);

        /** @var User $user */
        $user = $this->container->getByType(User::class);
        $user->login($payload->username, $payload->password);
        Assert::true($user->isLoggedIn());

        $logoutResponse = $this->runPresenter('Login', 'logout');
        Assert::type(RedirectResponse::class, $logoutResponse);
        Assert::false($user->isLoggedIn());
    }
}

(new AuthFlowTest())->run();

