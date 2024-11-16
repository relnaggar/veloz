<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Decorators;

interface DecoratorInterface
{
  /**
   * This method should return an array of new template variables that will be
   * added to the template variables array that is passed to the template
   * engine. This method should not modify the existing template variables.
   *
   * @param array $templateVars The existing template variables
   * @return array The new template variables to be added to the existing
   *   template variables. Must not contain any keys that are already present in
   *   the existing template variables.
   */
  public function getNewTemplateVars(array $templateVars): array;
}
