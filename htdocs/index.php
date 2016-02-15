<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

define('APP_DIR', __DIR__ . '/../app');

define('HTDOCS_DIR', __DIR__ . '/../htdocs');

define('USER_AVATAR_DIR', __DIR__ . '/../private/images/user/profilePhoto');


$container = require __DIR__ . '/../app/bootstrap.php';

$container->getByType('Nette\Application\Application')->run();
