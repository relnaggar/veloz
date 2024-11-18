<?php

declare(strict_types=1);

namespace Relnaggar\Veloz;

use DI\{
  Container,
  ContainerBuilder,
};
use Relnaggar\Veloz\{
  Routing\RouterInterface,
  Controllers\AbstractController,
  Decorators\DecoratorInterface,
  Routing\ControllerAction,
  Views\Page,
};

use function DI\{
  value,
  autowire,
};

abstract class AbstractApp
{
  private static ?Container $container = null;

  public function __construct()
  {
    $diDefinitions = [];

    // add the router to the DI definitions if it's set by the subclass
    $router = $this->getRouter();
    if (isset($router)) {
      $diDefinitions[RouterInterface::class] = value($router);
    }

    // get the decorator map
    $decoratorMap = $this->getDecoratorMap();

    // validate the decorator map
    foreach ($decoratorMap as $controllerClass => $decoratorClasses) {
      if (! is_subclass_of($controllerClass, AbstractController::class)) {
        throw new \InvalidArgumentException(
          "Controller class $controllerClass must extend AbstractController"
        );
      }
      foreach ($decoratorClasses as $decoratorClass) {
        if (! is_subclass_of($decoratorClass, DecoratorInterface::class)) {
          throw new \InvalidArgumentException(
            "Decorator class $decoratorClass must implement DecoratorInterface"
          );
        }
      }
    }

    // add the decorator map to the DI definitions
    foreach ($decoratorMap as $controllerClass => $decoratorClasses) {
      $diDefinitions[$controllerClass] = autowire()
        ->constructorParameter(
          'decorators',
          array_map('\DI\get', $decoratorClasses)
        );
    }

    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions($diDefinitions);
    self::$container = $containerBuilder->build();
  }

  /**
   * Returns the dependency injection container (PHP-DI Container).
   */
  public static function getContainer(): Container
  {
    if (self::$container === null) {
      throw new \Error('Container has not been initialized');
    }
    return self::$container;
  }

  /**
   * Override this method to tell the dependency injection container which
   * router to inject when a RouterInterface is requested.
   *
   * @return RouterInterface The router to use.
   */
  protected function getRouter(): ?RouterInterface
  {
    return null;
  }

  /**
   * Override this method to tell the dependency injection container which
   * controllers should be decorated by which decorators.
   *
   * @return array An associative array that maps controller classes to arrays
   *   of decorator classes. Must be in the format:
   *  [ 'ControllerClass' => [ 'DecoratorClass1', 'DecoratorClass2', ... ] ]
   *  Classes must be fully qualified class names, e.g. via ::class.
   */
  protected function getDecoratorMap(): array
  {
    return [];
  }

  /**
   * Routes to a \Relnaggar\Veloz\ControllerAction object, which contains the
   * controller and action to be called when the user navigates to the given
   * path using the given HTTP method.
   *
   * Override this method to define how the app should route requests. If you
   * don't override this method, the default implementation will use the router
   * injected by the getRouter method.
   *
   * @param string $serverRequestPath The URL path, not including the query
   *  string.
   * @param string $httpMethod The HTTP method e.g. GET, POST, PUT, DELETE
   * @return ControllerAction The controller and action to be called when the
   *   user navigates to the given path using the given HTTP method.
   */
  protected function route(
    string $serverRequestPath,
    string $httpMethod
  ): ControllerAction {
    $router = self::$container->get(RouterInterface::class);
    return $router->route($serverRequestPath, $httpMethod);
  }

  /**
   * Returns the current path.
   *
   * @return string The current path
   */
  public static function getCurrentPath(): string
  {
    return explode('?', $_SERVER['REQUEST_URI'])[0];
  }

  public final function run(): void
  {
    // get the URL path, not including the query string
    $serverRequestPath = self::getCurrentPath();

    // get the HTTP method e.g. GET, POST, PUT, DELETE
    $httpMethod = $_SERVER['REQUEST_METHOD'];

    // get the controller and action to call
    $controllerAction = $this->route($serverRequestPath, $httpMethod);

    // call the controller action
    $page = $controllerAction->getPage();

    // make sure the controller action returns a Page object
    if (! $page instanceof Page) {
      $controllerClass = $controllerAction->controllerClass;
      $action = $controllerAction->action;
      throw new \Error("Controller action $controllerClass->$action must return
        an instance of \\Relnaggar\Veloz\\Views\\Page");
    }
    // output the page content
    echo $page->getHtmlContent();
  }
}
