<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Controllers;

use ReflectionClass;
use Relnaggar\Veloz\{
  Decorators\DecoratorInterface,
  Config,
  Views\Page,
  Data\SectionInterface,
  Views\TemplateEngine,
};

abstract class AbstractController
{
  private array $decorators;

  public function __construct(array $decorators = [])
  {
    foreach ($decorators as $decorator) {
      if (!$decorator instanceof DecoratorInterface) {
        throw new \Error(
          'All decorators must implement the DecoratorInterface.'
        );
      }
    }
    $this->decorators = $decorators;
  }

  /**
   * Get the name of the controller class.
   *
   * @return string The name of the controller class.
   */
  public function getControllerName(): string
  {
    return (new ReflectionClass($this))->getShortName();
  }

  /**
   * Get the full path to the template file for the given controller.
   *
   * @param string $relativeTemplatePath The path to the template file,
   *   relative to the controller's template directory. Given without the file
   *   extension.
   * @return string The full path to the template file.
   */
  public function getFullTemplateFilePath(string $relativeTemplatePath): string
  {
    $config = Config::getInstance();
    $sourceDirectory = $config->get('sourceDirectory');
    $templateRootDirectory = $config->get('templateRootDirectory');
    $templateFileExtension = $config->get('templateFileExtension');
    $controllerName = $this->getControllerName();
    $templateFilePath = "$sourceDirectory/$templateRootDirectory/" .
      "$controllerName/$relativeTemplatePath{$templateFileExtension}";
    return $templateFilePath;
  }

  /**
   * Create a new Page instance with the HTML content loaded from the
   * layout template. The $bodyContent template variable is injected by
   * specifying the body template in this controller's template directory.
   * Additional variables can then be injected which will be available to both
   * the layout and body templates.
   *
   * @param string $relativeBodyTemplatePath The path to the body template file,
   *   relative to this controller's template directory, which is a subdirectory
   *   of the configured template root directory and named after the controller.
   *   Given without the file extension.
   *
   *   For example, if the controller class is named 'Home', the configured
   *   templateRootDirectory is 'templates', and $bodyTemplatePath is
   *   given as 'index', then the body template file will be loaded from
   *  'sourceDirectory/templates/Home/index.html.php'.
   * @param array $templateVars The variables to inject to the layout template
   *   and/or the body template. Must be in the format
   *   ['variableName' => 'variableValue', ...].
   * @param string $layoutTemplatePath The path to the layout template file,
   *   relative to the configured template root directory. Given without the
   *   file extension. If empty, the configured layout template path is used.
   * @param array $sections An array of SectionInterface instances to inject
   *   into the template. Each section will have its HTML content loaded from
   *   the template file specified by the SectionInterface implementation.
   * @param string $fullBodyTemplatePath The full path to the body template
   *   file. If specified, this is used instead of loading the body template
   *   from this controller's template directory. Cannot be used together with
   *   $relativeBodyTemplatePath.
   * @return Page A new Page instance with the HTML content loaded from the
   *   layout file, the body content injected, and the specified variables
   *   injected.
   */
  public function getPage(
    string $relativeBodyTemplatePath = '',
    array $templateVars = [],
    string $layoutTemplatePath = '',
    array $sections = [],
    string $fullBodyTemplatePath = '',
  ): Page {
    // apply decorators
    $templateVars = $this->applyDecorators($templateVars);

    // apply sections
    $controllerName = $this->getControllerName();
    foreach ($sections as $section) {
      if (!$section instanceof SectionInterface) {
        throw new \Error('All sections must implement the SectionInterface.');
      }
      $section->setHtmlContent(
        TemplateEngine::loadTemplate(
          $section->getTemplatePath($controllerName),
          $templateVars,
        )
      );
    }
    if (!empty($sections)) {
      $templateVars['sections'] = $sections;
    }

    if (!empty($fullBodyTemplatePath) && !empty($relativeBodyTemplatePath)) {
      throw new \Error(
        'Cannot specify both fullBodyTemplatePath and '
        . 'relativeBodyTemplatePath.'
      );
    } else if (!empty($fullBodyTemplatePath)) {
      // load the body template content from the full path
      $bodyTemplatePath = $fullBodyTemplatePath;
    } else {
      // load the body template content from the controller's template directory
      $bodyTemplatePath = "$controllerName/$relativeBodyTemplatePath";
    }

    // build the page
    return Page::withLayout(
      $bodyTemplatePath,
      $templateVars,
      $layoutTemplatePath
    );
  }

  /**
   * Redirect the user to the specified path.
   *
   * @param string $path The path to redirect the user to.
   * @param int $statusCode The HTTP status code to use for the redirect.
   */
  public function redirect(string $path, int $statusCode): void
  {
    if ($statusCode < 300 || $statusCode > 399) {
      throw new \Error('Invalid status code for redirect.');
    }

    if ($statusCode === 302) {
      header('Location: ' . $path);
    } else {
      header('Location: ' . $path, true, $statusCode);
    }
    exit();
  }

  private function applyDecorators(array $templateVars): array
  {
    foreach ($this->decorators as $decorator) {
      $newTemplateVars = $decorator->getNewTemplateVars($templateVars);
      foreach ($newTemplateVars as $key => $value) {
        if (array_key_exists($key, $templateVars)) {
          throw new \Error(
            'Decorators cannot modify existing template variables.'
          );
        }
        $templateVars[$key] = $value;
      }
    }
    return $templateVars;
  }

  /**
   * Hook method called before each action method is executed.
   * Can be overridden in subclasses to perform common setup tasks.
   * 
   * @param string $action The action method to be executed.
   */
  public function beforeAction(string $action): void {}
}
