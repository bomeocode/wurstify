<?php if (auth()->user()): ?>
  <!-- Bottom-Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-bottom pb-4" style="background-color: #3a2e28;">
    <div class="container-fluid justify-content-around">

      <a class="nav-link nav-feedback text-center text-white" href="<?= site_url('dashboard') ?>">
        <?php echo $currentController == 'Dashboard' ? '<i class="bi bi-house-door-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-house-door fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Start</small>
      </a>

      <a class="nav-link nav-feedback text-center text-white" href="<?= site_url('feed') ?>">
        <?php echo $currentController == 'Feed' ? '<i class="bi bi-postcard-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-postcard fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Feed <span id="feed-badge" class="badge rounded-pill bg-danger" style="display: none;"></span></small>
      </a>

      <a class="nav-link nav-feedback text-center text-white open-modal-form" href="#" data-url="<?= site_url('feedback') ?>">
        <?php echo $currentController == 'Merch' ? '<i class="bi bi-chat-quote-fill fs-4 d-block mx-auto mb-1"></i>' : '<i class="bi bi-chat-quote fs-4 d-block mx-auto mb-1"></i>';
        ?>
        <small>Feedback</small>
      </a>

    </div>
  </nav>
<?php endif; ?>