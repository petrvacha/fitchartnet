<?php
declare(strict_types=1);

use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;
use Tests\BaseTestCase;

require __DIR__ . '/../BaseTestCase.php';

final class PageSmokeTest extends BaseTestCase
{
    public function testHomepageRedirectsToLogin(): void
    {
        $response = $this->runPresenter('Homepage');
        Assert::type(RedirectResponse::class, $response);
    }

    public function testLoginPageRenders(): void
    {
        $response = $this->runPresenter('Login');
        Assert::type(TextResponse::class, $response);
    }

    public function testRegistrationPageRenders(): void
    {
        $response = $this->runPresenter('Registration');
        Assert::type(TextResponse::class, $response);
    }

    public function testChallengeListAfterLogin(): void
    {
        $this->loginDefaultUser();
        $response = $this->runPresenter('Challenge');
        Assert::type(TextResponse::class, $response);
    }
}

(new PageSmokeTest())->run();

