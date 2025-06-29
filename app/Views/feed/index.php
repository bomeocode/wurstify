<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Wurst-Feed<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <h1 class="mb-4">Der Wurst-Feed</h1>
  <p class="text-muted">Die neuesten Bewertungen aus der ganzen Republik.</p>

  <div id="feed-list"></div>
  <div id="loading-indicator" class="text-center my-4" style="display: none;">
    <div class="spinner-border" role="status"></div>
  </div>
  <div id="load-more-trigger"></div>
</div>
<?= $this->endSection() ?>