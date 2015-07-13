<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

define('APP_DIR', __DIR__ . '/../app');

$container = require __DIR__ . '/../app/bootstrap.php';

$container->getByType('Nette\Application\Application')->run();
