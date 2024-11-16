<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (isset($metaDescription)): ?>
      <meta name="description" content="<?= $metaDescription ?>">
    <?php endif ?>
    <?php if (isset($metaRobots)): ?>
      <meta name="robots" content="<?= $metaRobots ?>">
    <?php endif ?>
    <title><?= $title ?? 'Untitled' ?></title>
  </head>
  <body>
    <?= $bodyContent ?>
  </body>
</html>
