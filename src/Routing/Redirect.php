<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Routing;

use Relnaggar\Veloz\Controllers\RedirectController;

class Redirect extends ControllerAction
{
  /**
   * Create a new Redirect instance that redirects to the specified URL.
   *
   * @param string $url The URL to redirect to.
   * @param int $statusCode The HTTP status code to use for the redirect.
   */
  public function __construct(string $url, int $statusCode = 302)
  {
    parent::__construct(RedirectController::class, 'doRedirect', [
      $url,
      $statusCode
    ]);
  }
}
