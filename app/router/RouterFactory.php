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
            $router[] = new Route('', 'Homepage:launch');
            $router[] = new Route('/gamma', 'Homepage:default');
            $router[] = new Route('/registration/confirm/<hash>', 'Registration:check');

            $router[] = new Route('/api/documentation', 'Api:documentation');
            
            $router[] = new Route('/api[/v<apiVersion>]', [
                    'presenter' => 'Api',
                    'action' => 'test',
                    'apiVersion' => NULL
            ]);
            $router[] = new Route('/api[/v<apiVersion>[/<apiToken>[/<actionType>[/<activityId>[/<value>[/datetime]]]]]]', [
                    'presenter' => 'Api',
                    'action' => 'process',
                    'apiVersion' => NULL,
                    'apiToken' => NULL,
                    'actionType' => NULL,
                    'activityId' => NULL,
                    'value' => NULL,
                    'datetime' => NULL
            ]);
            $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:launch');
            
            return $router;
        }

        return $router;
    }


}