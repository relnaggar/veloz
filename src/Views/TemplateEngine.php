<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Views;

use Relnaggar\Veloz\Config;

class TemplateEngine
{
  /**
    * Load a template file, inject variables into it, and return the result.
    *
    * @param string $templatePath The path to the template file, relative to
    *   the configured template root directory. Given without the file
    *   extension.
    * @param array $templateVars The variables to inject. Must be in the format
    *   ['variableName' => 'variableValue', ...].
    * @param string $templateDirectory The directory of the template file. If
    *   empty, the configured template root directory is used.
    * @return string The contents of the template file with the variables
    *   injected.
    */
  public static function loadTemplate(
    string $templatePath,
    array $templateVars = [],
    string $templateDirectory = ''
  ): string {
    $config = Config::getInstance();

    // use the configured template root directory if none is given
    if (empty($templateDirectory)) {
      $templateDirectory = $config->get('templateRootDirectory');
    }

    // extract the variables to be injected
    extract($templateVars);

    $filePath = $config->get('sourceDirectory') . '/' . $templateDirectory .
      '/' . $templatePath . $config->get('templateFileExtension');

    if (file_exists($filePath)) {
      // start output buffering to capture the template contents
      ob_start();
      // load the template file
      require $filePath;
      // return the contents of the output buffer
      return ob_get_clean();
    }

    return '';
  }

  /*
    * Get a snippet of the initial text from a HTML template.
    *
    * @param string $templatePath The path to the template file, relative to
    *   the configured template root directory. Given without the file
    *   extension.
    * @param int $numberOfWords The number of words to include in the snippet.
    *   If -1, the full text is returned.
    * @param array $templateVars The variables to inject. Must be in the format
    *   ['variableName' => 'variableValue', ...].
    * @return string The snippet of text with ... appended if the snippet is
    *   shorter than the full text.
    */
  public static function getSnippet(
    string $templatePath,
    int $numberOfWords = -1,
    array $templateVars = [],
  ): string {
    $html = self::loadTemplate($templatePath, $templateVars);
    $text = strip_tags($html);

    // remove extra whitespace
    $squashedText = trim(preg_replace('/\s+/', ' ', $text));

    // return the full text if the number of words is -1
    if ($numberOfWords === -1) {
      return $squashedText;
    }

    // get the first $numberOfWords words
    $words = explode(' ', $squashedText);
    $snippet = implode(' ', array_slice($words, 0, $numberOfWords));

    // add an ellipsis
    $snippet .= '...';

    return $snippet;
  }
}
