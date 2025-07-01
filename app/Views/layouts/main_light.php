<!doctype html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
  <title><?= $this->renderSection('title') ?></title>
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

  <script src="<?= base_url('assets/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
  <script type="module" src="/assets/js/main.js"></script>

  <?php
  try {
    $jsFileName = strtolower($currentController) . '.js';
    $jsFilePath = FCPATH . 'assets/js/' . $jsFileName;
    if (file_exists($jsFilePath)) {
      echo '<script type="module" src="/assets/js/' . $jsFileName . '"></script>';
    }
  } catch (\Throwable $e) {
    // --
  }
  ?>

  <div id="update-prompt" style="display:none; position: fixed; bottom: 20px; left: 20px; background-color: #333; color: white; padding: 15px; border-radius: 5px; z-index: 1000;">
    <span>Eine neue Version ist verfügbar!</span>
    <button id="reload-button" style="margin-left: 15px; background-color: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Neu laden</button>
  </div>

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('https://app.wurstify.com/sw.js')
          .then(registration => {
            console.log('Service Worker registriert.');

            const showUpdatePrompt = (worker) => {
              const updatePrompt = document.getElementById('update-prompt');
              const reloadButton = document.getElementById('reload-button');
              reloadButton.onclick = () => {
                worker.postMessage({
                  type: 'SKIP_WAITING'
                });

                window.location.reload();
              };

              updatePrompt.style.display = 'block';
              console.log('Neuer Service Worker wartet. Update-Benachrichtigung wird angezeigt.');
            };

            if (registration.waiting) {
              showUpdatePrompt(registration.waiting);
            }

            registration.onupdatefound = () => {
              const installingWorker = registration.installing;
              if (installingWorker) {
                installingWorker.onstatechange = () => {
                  if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    if (registration.waiting) {
                      showUpdatePrompt(registration.waiting);
                    }
                  }
                };
              }
            };
          })
          .catch(error => {
            console.log('Service Worker Registrierung fehlgeschlagen:', error);
          });
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
  <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
  <?= $this->renderSection('scripts') ?>

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
  <?= $this->renderSection('scripts') ?>

</body>

</html>