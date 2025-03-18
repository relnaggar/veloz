<?php

declare(strict_types=1);

namespace Example;

use Relnaggar\Veloz\{
  AbstractApp,
  Routing\ControllerAction,
  Routing\Redirect,
};

class App extends AbstractApp
{
  public function route(
    string $path,
    string $method,
  ): ControllerAction {
    if ($path === '/') {
      return new ControllerAction(Controllers\Home::class, 'index');
    } else if ($path === '/temporary-redirect') {
      return new Redirect('https://google.com', 302);
    } else if ($path === '/permanent-redirect') {
      return new Redirect('https://google.com', 301);
    } else {
      return new ControllerAction(Controllers\Home::class, 'notFound');
    }
  }
}
