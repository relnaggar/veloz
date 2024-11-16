<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php'; // composer

use Relnaggar\Veloz\Config;
Config::getInstance()->set('sourceDirectory', __DIR__);

require_once __DIR__ . '/../autoload.php'; // for Example

use Example\App;

$app = new App();
$app->run();
