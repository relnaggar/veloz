<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Routing;

interface RouterInterface
{
  /**
   * Routes to a \Relnaggar\Veloz\Routing\ControllerAction object, which
   * contains the controller and action to be called when the user navigates to
   * the given path using the given HTTP method.
   *
   * @param string $serverRequestPath The URL path, not including the query
   *   string.
   * @param string $httpMethod The HTTP method e.g. GET, POST, PUT, DELETE
   */
  public function route(
    string $serverRequestPath,
    string $httpMethod,
  ): ControllerAction;

  /**
   * Checks if the given path is a valid route.
   *
   * @param string $serverRequestPath The URL path, not including the query
   *  string.
   * @return bool true if the path is a valid route, false otherwise
   */
  public function hasPath(string $serverRequestPath): bool;
}
