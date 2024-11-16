<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Routing;

class BasicRouter implements RouterInterface
{
  private array $routes;
  private ControllerAction $pageNotFound;

  /**
   * @param array $routes An associative array of routes, where the key is the
   *   route pattern and the value is an associative array of HTTP methods and
   *   \Relnaggar\Veloz\Routing\ControllerAction objects.
   * @param ControllerAction $pageNotFound The controller and action to be
   *   called when no route matches the given path.
   */
  public function __construct(array $routes, ControllerAction $pageNotFound)
  {
    // validate routes
    foreach ($routes as $pattern => $methods) {
      foreach ($methods as $method => $controllerAction) {
        if (!is_string($pattern)) {
          throw new \InvalidArgumentException('Route pattern must be a string');
        }
        if (!is_string($method)) {
          throw new \InvalidArgumentException('HTTP method must be a string');
        }
        if (!($controllerAction instanceof ControllerAction)) {
          throw new \InvalidArgumentException(
            'ControllerAction must be an instance of ControllerAction'
          );
        }
      }
    }

    $this->routes = $routes;
    $this->pageNotFound = $pageNotFound;
  }

  public function hasPath(string $serverRequestPath): bool
  {
    foreach ($this->routes as $pattern => $route) {
      // replace all <...> with (?P<...>[^/]+)
      $regx = preg_replace('/<(\w+)>/', '(?P<$1>[^/]+)', $pattern);
      $regx = "#^$regx$#";
      if (preg_match($regx, $serverRequestPath, $matches)) {
        return true;
      }
    }
    $match = array_key_exists($serverRequestPath, $this->routes);
    return $match;
  }

  public function route(
    string $serverRequestPath,
    string $httpMethod,
  ): ControllerAction {
    foreach ($this->routes as $pattern => $route) {
      // replace all <...> with (?P<...>[^/]+)
      $regx = preg_replace('/<(\w+)>/', '(?P<$1>[^/]+)', $pattern);
      $regx = "#^$regx$#";
      if (preg_match($regx, $serverRequestPath, $matches)) {
        $matchingPattern = $pattern;
        break; // stop at first match to avoid $matches being overwritten
      }
    }

    if (!isset($matchingPattern)) { // no match
      return $this->pageNotFound;
    } else if (!isset($this->routes[$matchingPattern][$httpMethod])) {
      return new MethodNotAllowed(array_keys($this->routes[$matchingPattern]));
    } else { // match and correct method
      if (count($matches) == 1) { // no <...> matches
        return $this->routes[$matchingPattern][$httpMethod];
      } else { // 1 or more <...> matches
        $namedMatches = [];
        foreach ($matches as $key => $value) {
          if (is_string($key)) {
            $namedMatches[$key] = $value;
          }
        }
        return new ControllerAction(
          $this->routes[$matchingPattern][$httpMethod]->controllerClass,
          $this->routes[$matchingPattern][$httpMethod]->action,
          $namedMatches // pass <...> matches as parameters
        );
      }
    }
  }
}
