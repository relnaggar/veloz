<?php

declare(strict_types=1);

# if the requested file exists, serve it
$filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($filePath)) {
  return false; // serve the requested file as-is
}

// route requests to index.php if the file does not exist
require __DIR__ . '/index.php';
