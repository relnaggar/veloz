<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Data;

interface SectionInterface
{
  /**
   * Get the path where the template file is located for this section. This
   * should be relative to the configured template root directory.
   * 
   * @param string $controllerName The name of the controller that is
   *  responsible for this section.
   * @return string The path to the template file.
   */
  public function getTemplatePath(string $controllerName): string;

  /**
   * Set the rendered content for this section. This is used internally by the
   * framework and is not intended to be called within the application code, but
   * must be implemented by the application. The simplest implementation is to
   * store the content in a property.
   * 
   * @param string $content The rendered content for this section.
   */
  public function setHtmlContent(string $htmlContent): void;
  
  /**
   * Get the rendered content for this section. This can be used by the
   * application's templates to include the section content in the final output.
   * The simplest implementation is to return the content stored in the
   * property set by setHtmlContent().
   * 
   * @return string The rendered content for this section.
   */
  public function getHtmlContent(): string;
}
