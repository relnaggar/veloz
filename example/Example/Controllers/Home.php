<?php

declare(strict_types=1);

namespace Example\Controllers;

use Relnaggar\Veloz\{
  Controllers\AbstractController,
  Views\Page,
};

class Home extends AbstractController
{
  public function index(): Page
  {
    return $this->getPage(
      __FUNCTION__,
      [
        'title' => 'Home',
        'metaDescription' => 'This is the home page.'
      ]
    );
  }

  public function notFound(): Page
  {
    return $this->getPage(
      __FUNCTION__,
      [
        'title' => 'Not Found',
      ]
    );
  }
}
