<?php if (auth()->user()): ?>
  <!-- Bottom-Navigation -->
  <nav class="navbar navbar-expand fixed-bottom pb-4" style="background-color: #1B5071;">
    <div class="container-fluid justify-content-around">

      <a class="nav-link text-center text-white" href="<?= site_url('dashboard') ?>">
        <?php echo $currentController == 'Dashboard' ? '<i class="bi bi-house-door-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-house-door fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Start</small>
      </a>

      <a class="nav-link text-center text-white" href="<?= site_url('screens') ?>">
        <?php echo $currentController == 'Screens' ? '<i class="bi bi-display-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-display fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Bildschirme</small>
      </a>

      <a class="nav-link text-center text-white" href="<?= site_url('layouts') ?>">
        <?php echo $currentController == 'Layouts' ? '<i class="bi bi-grid-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-grid fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Layouts</small>
      </a>

      <a class="nav-link text-center text-white" href="<?= site_url('media') ?>">
        <?php echo $currentController == 'Media' ? '<i class="bi bi-collection-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-collection fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Medien</small>
      </a>

      <a class="nav-link text-center text-white" href="<?= site_url('settings') ?>">
        <?php echo $currentController == 'Settings' ? '<i class="bi bi-gear-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-gear fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Einstellungen</small>
      </a>

    </div>
  </nav>
<?php endif; ?>