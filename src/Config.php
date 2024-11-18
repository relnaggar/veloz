<?php

declare(strict_types=1);

namespace Relnaggar\Veloz;

class Config
{
  // singleton pattern

  private static ?Config $instance = null;

  private function __construct() {}

  public static function getInstance(): Config
  {
    if (self::$instance === null) {
      self::$instance = new Config();
    }
    return self::$instance;
  }

  // defaults
  private $settings = [
    'sourceDirectory' => __DIR__ . '/../',
    'templateRootDirectory' => 'templates',
    'templateFileExtension' => '.html.php',
    'layoutTemplatePath' => 'layout',
  ];

  public function set($key, $value)
  {
    $this->settings[$key] = $value;
  }

  public function get($key, $default = null)
  {
    return $this->settings[$key] ?? $default;
  }
}
