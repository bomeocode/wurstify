<?php
$fullController    = service('router')->controllerName();
$currentController = substr(strrchr($fullController, '\\'), 1);
?>
<!doctype html>
<html lang="de">

<head>
  <meta charset="utf-8">
  <title><?= $this->renderSection('title') ?></title>
  <link rel="stylesheet" href="<?= base_url('css/light.css') ?>">
</head>

<body>
  <?= $this->include('partials/header') ?>

  <main>
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->include('partials/footer') ?>
  <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>

</html>