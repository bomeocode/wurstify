<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Mein Geschäft: <?= esc($vendor['name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">

      <div class="text-center mb-5">
        <h1 class="h2">Willkommen im Partner-Portal</h1>
        <p class="lead text-muted">Hier können Sie das öffentliche Profil für "<?= esc($vendor['name']) ?>" verwalten.</p>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Ihr aktuelles Profil</h5>
        </div>
        <div class="card-body">
          <p>Hier sehen Sie eine Vorschau Ihrer aktuellen Daten. Klicken Sie auf "Profil bearbeiten", um Änderungen vorzunehmen.</p>

          <dl class="row">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9"><?= esc($vendor['name']) ?></dd>

            <dt class="col-sm-3">Adresse</dt>
            <dd class="col-sm-9"><?= esc($vendor['address']) ?></dd>

            <dt class="col-sm-3">Kategorie</dt>
            <dd class="col-sm-9 text-capitalize"><?= esc($vendor['category']) ?></dd>

            <dt class="col-sm-3">Webseite</dt>
            <dd class="col-sm-9"><?= $vendor['website_url'] ? '<a href="' . esc($vendor['website_url'], 'attr') . '" target="_blank">' . esc($vendor['website_url']) . '</a>' : 'Nicht angegeben' ?></dd>
          </dl>

          <div class="d-grid mt-4">
            <a href="<?= route_to('vendor_edit') ?>" class="btn btn-primary btn-lg">
              Profil jetzt bearbeiten
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?= $this->endSection() ?>