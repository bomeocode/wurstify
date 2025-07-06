<?= $this->extend('layouts/main_light') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
  <div class="row">

    <div class="col-md-3 col-lg-2">
      <?= $this->include('admin/partials/sidebar') ?>
    </div>

    <div class="col-md-9 col-lg-10">
      <?= $this->renderSection('main') ?>
    </div>

  </div>
</div>
<?= $this->endSection() ?>