<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Bewerte <?= esc($vendor->name) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <div class="text-center">
    <h1 class="h3">Bewerte jetzt</h1>
    <p class="lead">"<?= esc($vendor->name) ?>"</p>
  </div>

  <?= view('partials/rating_form', ['vendor' => $vendor, 'is_qr_code' => true]) ?>
</div>
<?= $this->endSection() ?>