<?php if (auth()->user()): ?>
  <!-- Bottom-Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-wurstify fixed-bottom pb-4" style="color: #3a2e28; background-color: #ffc107;">
    <div class="container-fluid justify-content-around">

      <a class="nav-link text-center" href="<?= site_url('dashboard') ?>">
        <?php echo $currentController == 'Dashboard' ? '<i class="bi bi-house-door-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-house-door fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Start</small>
      </a>

      <a class="nav-link text-center" href="<?= site_url('ratings') ?>">
        <?php echo $currentController == 'Ratings' ? '<i class="bi bi-star-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-star fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Bewerten</small>
      </a>

      <a class="nav-link text-center" href="<?= site_url('feed') ?>">
        <?php echo $currentController == 'Feed' ? '<i class="bi bi-postcard-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-postcard fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Feed</small>
      </a>

      <a class="nav-link text-center" href="<?= site_url('merch') ?>">
        <?php echo $currentController == 'Merch' ? '<i class="bi bi-bag-heart-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-bag-heart fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Merch</small>
      </a>

    </div>
  </nav>
<?php endif; ?>