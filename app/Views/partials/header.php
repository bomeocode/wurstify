<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="/assets/css/style.css">

  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#3367D6">
</head>

<body>
  <?php if (auth()->user()): ?>
    <div class="mb-3">
      <nav class="navbar navbar-expand-lg fixed-top shadow-sm" style="background-color: #e0f0ff;">
        <div class="container-fluid">

          <a class="navbar-brand" href="#">
            EduScreen
          </a>

          <?php if ($currentController == 'Screens'): ?>
            <a class="navbar-brand" href="<?= base_url('/') ?>">
              <i class="bi bi-chevron-left"></i> Bildschirme
            </a>
          <?php endif; ?>

          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarWurst" aria-controls="navbarWurst" aria-expanded="false" aria-label="Navigation umschalten">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarWurst">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('me') ?>">Profil</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('account') ?>">Konto</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url('logout') ?>">Logout</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  <?php endif; ?>

  <div class="container pb-5">