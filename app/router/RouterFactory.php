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
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
        }

        return $router;
    }


}