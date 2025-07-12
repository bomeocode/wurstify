<!doctype html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
  <title><?= $this->renderSection('title') ?></title>
  <?php if (auth()->loggedIn()): ?>
    <meta name="user-id" content="<?= auth()->id() ?>">
  <?php endif; ?>
  <link rel="icon" type="image/png" sizes="32x32" href="https://app.wurstify.com/assets/icons/icon-192x192.png">
  <meta name="X-CSRF-TOKEN-NAME" content="<?= csrf_header() ?>">
  <meta name="X-CSRF-TOKEN-VALUE" content="<?= csrf_hash() ?>">

  <?= csrf_meta() ?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
  <link rel="stylesheet" href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <?php if ($currentController == 'Dashboard'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
  <?php endif; ?>
  <link rel="manifest" href="<?= base_url('manifest.json') ?>">
  <meta name="theme-color" content="#ffc107">
</head>

<body style="padding-top: 56px; padding-bottom: 56px; ">

  <div id="update-prompt" style="display: none; z-index: 2000; position: sticky; top: 0;" class="alert alert-primary m-0 rounded-0 border-0 border-bottom" role="alert">
    <span>Eine neue Version von Wurstify ist verfügbar!</span>
    <button id="reload-button" class="btn btn-sm btn-primary ms-3">Jetzt aktualisieren</button>
  </div>

  <?= $this->include('partials/header', ['currentController' => $currentController]) ?>

  <main>
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->include('partials/footer') ?>

  <div class="toast-container position-fixed top-0 end-0 p-3">
    <?php if (session()->has('toast')) : ?>
      <?php
      // Hole die Toast-Daten aus der Session
      $toast = session('toast');
      $message = $toast['message'];
      $type = $toast['type'] ?? 'info'; // Standard-Typ ist 'info'

      // Weise den Typen die passenden Bootstrap-Klassen zu
      $toastClass = [
        'success' => 'bg-success text-white',
        'danger'  => 'bg-danger text-white',
        'warning' => 'bg-warning text-dark',
        'info'    => 'bg-info text-dark',
        'error'  => 'bg-danger text-white',
      ][$type];
      ?>

      <div id="liveToast" class="toast <?= $toastClass ?>" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header <?= $toastClass ?> border-0">
          <strong class="me-auto">System-Meldung</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= esc($message) ?>
        </div>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const toastEl = document.getElementById('liveToast');
          if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
              delay: 3000 // Toast verschwindet nach 3 Sekunden
            });
            toast.show();
          }
        });
      </script>

    <?php endif; ?>
  </div>

  <div class="modal fade" id="ajax-modal" tabindex="-1" aria-labelledby="ajax-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ajax-modal-label">Lade...</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="ajax-modal-body">
          <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="offcanvas offcanvas-start" tabindex="-1" id="ajax-offcanvas" aria-labelledby="ajax-offcanvas-label">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="ajax-offcanvas-label">Lade...</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="ajax-offcanvas-body">
      <div class="text-center p-5">
        <div class="spinner-border" role="status"></div>
      </div>
    </div>
  </div>

  <script src="<?= base_url('assets/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script type="module" src="/assets/js/app.js"></script>

  <?php
  try {
    $controllerName = strtolower(substr(strrchr(service('router')->controllerName(), '\\'), 1));
    $jsFileName = $controllerName . '.js';
    $jsFilePath = FCPATH . 'assets/js/' . $jsFileName;

    if (file_exists($jsFilePath)) {
      echo '<script type="module" src="/assets/js/' . $jsFileName . '"></script>';
    }
  } catch (\Throwable $e) {
    // Fehler ignorieren, wenn die Route nicht ermittelt werden kann
  }
  ?>

  <?= $this->renderSection('scripts') ?>
</body>

</html>