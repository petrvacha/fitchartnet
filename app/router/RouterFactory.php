<?php

namespace App;

use Nette\Application\Routers\CliRouter;
use Nette\Application\Routers\Route,
    Nette\Application\Routers\RouteList;

/**
 * Router factory.
 */
class RouterFactory
{
    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter($consoleMode)
    {
        $router = new RouteList();
        if ($consoleMode) {
            $router[] = new CliRouter(['action' => 'Cli:Cli:cron']);
        } else {
            $router = new RouteList();
            $router[] = new Route('', 'Homepage:launch');
            $router[] = new Route('/about', 'Homepage:about');
            $router[] = new Route('/login', 'Login:default');
            $router[] = new Route('/registration', 'Registration:default');
            $router[] = new Route('/registration/confirm/<hash>', 'Registration:check');
            $router[] = new Route('/reset-password', 'Login:resetPassword');
            $router[] = new Route('/new-password/<token>', 'Login:newPassword');
            $router[] = new Route('/last', 'Homepage:last');
            $router[] = new Route('/invitation/<challengeId>/<hash>', 'Registration:invitation');
            $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:launch');
            
            return $router;
        }

        return $router;
    }
}
