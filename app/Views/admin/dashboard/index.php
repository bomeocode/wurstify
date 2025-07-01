<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Admin Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Admin Dashboard</h1>
    <span class="badge bg-danger">Admin-Bereich</span>
  </div>

  <div class="row">

    <div class="col-md-4 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <h5 class="card-title">Benutzer</h5>
          <p class="display-4 fw-bold"><?= esc($userCount) ?></p>
          <a href="<?= site_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">Benutzer verwalten</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <h5 class="card-title">Anbieter (Vendors)</h5>
          <p class="display-4 fw-bold"><?= esc($vendorCount) ?></p>
          <a href="<?= site_url('admin/vendors') ?>" class="btn btn-outline-secondary btn-sm">Anbieter verwalten</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <h5 class="card-title">Bewertungen</h5>
          <p class="display-4 fw-bold"><?= esc($ratingCount) ?></p>
          <a href="<?= site_url('admin/ratings') ?>" class="btn btn-outline-secondary btn-sm">Bewertungen verwalten</a>
        </div>
      </div>
    </div>

  </div>

</div>
<?= $this->endSection() ?>