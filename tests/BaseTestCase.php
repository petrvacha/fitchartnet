<?php
declare(strict_types=1);

namespace Tests;

use Nette\Application\IPresenterFactory;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;
use Nette\Security\User;
use Tester\TestCase;

require_once __DIR__ . '/bootstrap.php';

abstract class BaseTestCase extends TestCase
{
    /** @var \Nette\DI\Container */
    protected $container;

    protected function setUp(): void
    {
        $_COOKIE = []; // Clear superglobal array
        parent::setUp();
        Bootstrap::resetDatabase();
        $this->refreshContainer();
    }

    protected function tearDown(): void
    {
        if ($this->container !== null && method_exists($this->container, 'getByType')) {
            $this->container->getByType(User::class)->logout(true);
        }
        
        // Close session if active to prevent conflicts in subsequent tests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        parent::tearDown();
    }

    protected function refreshContainer(array $cookies = []): void
    {
        // Close session before creating new container to prevent conflicts
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        $this->container = Bootstrap::createContainer($cookies);
        // Session will be started automatically by Nette when needed (e.g., during login)
        // $this->container->getByType(Session::class)->start();
    }

    protected function loginDefaultUser(string $username = 'test', string $password = '123456'): User
    {
        $user = $this->container->getByType(User::class);
        $user->logout(true);
        $user->login($username, $password);
        return $user;
    }

    protected function runPresenter(string $presenter, string $action = 'default', array $params = [], string $method = 'GET'): IResponse
    {
        $factory = $this->container->getByType(IPresenterFactory::class);
        $presenterInstance = $factory->createPresenter($presenter);

        if ($presenterInstance instanceof Presenter) {
            $presenterInstance->autoCanonicalize = false;
        }

        $request = new Request($presenter, $method, array_merge(['action' => $action], $params));
        return $presenterInstance->run($request);
    }
}

