<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Repositories;

use PDO;

interface DatabaseInterface
{
  /**
   * Get the PDO connection.
   * @return PDO The PDO connection.
   */
  public function getConnection(): PDO;
}
