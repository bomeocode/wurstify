<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Ihr QR-Code<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8 text-center">
      <h1 class="h2">Ihr persönlicher QR-Code</h1>
      <p class="lead text-muted">Stellen Sie diesen QR-Code an Ihrem Stand auf, damit Kunden Ihre Wurst schnell und einfach bewerten können!</p>

      <div class="card shadow-sm my-4">
        <div class="card-body p-4">
          <img src="<?= $qrCodeDataUri ?>" alt="Wurstify QR-Code" class="img-fluid">
        </div>
      </div>

      <p>Klicken Sie auf die Buttons, um Ihren Code herunterzuladen.</p>

      <div class="d-grid gap-2 d-md-flex justify-content-md-center">
        <a href="<?= $qrCodeDataUri ?>" download="wurstify-qr-code.png" class="btn btn-primary btn-lg">
          <i class="bi bi-download me-2"></i> QR-Code als Bild (.png)
        </a>

        <a href="#" class="btn btn-secondary btn-lg disabled" aria-disabled="true">
          <i class="bi bi-file-earmark-pdf me-2"></i> Schild als PDF (bald verfügbar)
        </a>
      </div>

      <div class="mt-4">
        <a href="<?= route_to('vendor_dashboard') ?>">&laquo; Zurück zum Dashboard</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>