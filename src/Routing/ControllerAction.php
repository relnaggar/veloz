<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Routing;

use Relnaggar\Veloz\{
  Controllers\AbstractController,
  Views\Page,
  AbstractApp,
};

class ControllerAction
{
  public readonly string $controllerClass;
  public readonly string $action;
  public readonly array $params;

  /**
   * Constructs a ControllerAction object, which is a simple pair of a
   * controller and an action.
   *
   * @param string $controllerClass The class of the controller to be called,
   *  which must extend AbstractController. Can be provided via the ::class.
   * @param string $action The action to be called on the controller.
   * @param array $params The parameters to be passed to the action.
   */
  public function __construct(
    string $controllerClass,
    string $action,
    array $params = []
  ) {
    if (!is_subclass_of($controllerClass, AbstractController::class)) {
      throw new \InvalidArgumentException(
        'Controller class must extend AbstractController'
      );
    }
    $this->controllerClass = $controllerClass;
    $this->action = $action;
    $this->params = $params;
  }

  /**
   * Calls the controller and action, passing the parameters, and returns the
   * resulting Page object.
   */
  public function getPage(): Page
  {
    $container = AbstractApp::getContainer();
    $controller = $container->get($this->controllerClass);
    $action = $this->action;
    return $controller->$action(...$this->params);
  }
}
