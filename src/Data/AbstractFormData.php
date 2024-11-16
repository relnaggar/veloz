<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Data;

abstract class AbstractFormData
{
  /**
   * Create a new AbstractFormData instance.
   *
   * @param array $data The key-value array of data to be set on the object.
   *   The keys given must match the property names of the extending class.
   */
  public function __construct(array $data = [])
  {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }

  /**
   * Validate the form data.
   *
   * @return array An associative array of errors, where the key is the property
   *   name and the value is the error message.
   */
  abstract public function validate(): array;
}
