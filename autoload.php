<?php

declare(strict_types=1);

use Relnaggar\Veloz\Config;

spl_autoload_register(function (string $className): void
{
  $sourceDirectory = Config::getInstance()->get('sourceDirectory');
  $fileName = str_replace('\\', '/', $className) . '.php';
  $file = "$sourceDirectory/$fileName";
  if (file_exists($file)) {
    require_once $file;
  }
});
