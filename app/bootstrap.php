<?php

require_once __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode($configurator->detectDebugMode());
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

if (\Nette\Environment::isConsole() && isset($_SERVER['argv'][0]) && Nette\Utils\Strings::endsWith($_SERVER['argv'][0], 'phpunit')) {
    throw new Nette\Neon\Exception('Test environment is not implemented.');
    //$enviroment = 'test';

} else {

    if (\Nette\Environment::isConsole()) {
        $enviroment = 'cron';

    } else {
        if (Nette\Utils\Strings::endsWith($_SERVER['SERVER_NAME'], 'gamma.fitchart.net')) {
            $enviroment = 'development';

        } elseif (Nette\Utils\Strings::endsWith($_SERVER['SERVER_NAME'], 'fitchart.net')) {
            $enviroment = 'production';

        } elseif ($configurator->isDebugMode()) {
            $enviroment = 'local';

        } else {
            throw new Nette\Neon\Exception('Enviroment has not been detected.');
        }
    }

    $configurator->addConfig(__DIR__ . '/config/config.neon', $enviroment);

    if (file_exists($configFile = __DIR__ . '/config/config.local.neon')) {
        $configurator->addConfig($configFile);

    } else {
        throw new Nette\Neon\Exception('File \'config.local.neon\' not found in ' . $configFile . '.');
    }
}

$container = $configurator->createContainer();

return $container;
