<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Controllers;

/**
 * Used internally by the Redirect class to redirect to a specified URL.
 */
class RedirectController extends AbstractController
{
  public function doRedirect(string $url)
  {
    $this->redirect($url);
  }
}
