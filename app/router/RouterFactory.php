<?php

namespace App;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route,
    Nette\Application\Routers\CliRouter;

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
            $router[] = new CliRouter(array('action' => 'Cli:Cli:cron'));

        } else {
            $router = new RouteList();
            $router[] = new Route('', 'Homepage:launch', Route::SECURED);
            $router[] = new Route('/login', 'Login:default', Route::SECURED);
            $router[] = new Route('/registration', 'Registration:default', Route::SECURED);
            $router[] = new Route('/registration/confirm/<hash>', 'Registration:check', Route::SECURED);
            $router[] = new Route('/reset-password', 'Login:resetPassword', Route::SECURED);
            $router[] = new Route('/new-password/<token>', 'Login:newPassword', Route::SECURED);
            $router[] = new Route('/last', 'Homepage:last', Route::SECURED);
            $router[] = new Route('/invitation/<challengeId>/<hash>', 'Registration:invitation', Route::SECURED);
            $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:launch', Route::SECURED);
            
            return $router;
        }

        return $router;
    }


}