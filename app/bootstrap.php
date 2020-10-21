<?php

require_once __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode($configurator->detectDebugMode());
$configurator->enableDebugger(__DIR__ . '/../log');
error_reporting(~E_USER_DEPRECATED);
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

if (Nette\Utils\Strings::endsWith($_SERVER['SERVER_NAME'], 'fitchart.net')) {
    $environment = 'production';

} elseif ($configurator->isDebugMode()) {
    $environment = 'local';

} else {
    throw new Nette\Neon\Exception('Environment has not been detected.');
}

$configurator->addConfig(__DIR__ . '/config/config.neon', $environment);

if (file_exists($configFile = __DIR__ . '/config/config.local.neon')) {
    $configurator->addConfig($configFile);

} else {
    throw new Nette\Neon\Exception('File config.local.neon is not found in ' . $configFile . '.');
}

$container = $configurator->createContainer();

return $container;
