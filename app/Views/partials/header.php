  <?php if (auth()->user()): ?>
    <div class="mb-3">
      <nav class="navbar navbar-dark navbar-expand-lg fixed-top shadow-sm" style="background-color: #3a2e28;">
        <div class="container-fluid">

          <a class="navbar-brand" href="#">
            Wurstify
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
                <a class="nav-link" href="<?= base_url('settings') ?>">Einstellungen</a>
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