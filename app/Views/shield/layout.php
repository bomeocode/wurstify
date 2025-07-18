<!doctype html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
    <title><?= $this->renderSection('title') ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="https://app.wurstify.com/assets/icons/icon-192x192.png">
    <meta name="X-CSRF-TOKEN-NAME" content="<?= csrf_header() ?>">
    <meta name="X-CSRF-TOKEN-VALUE" content="<?= csrf_hash() ?>">

    <link rel="stylesheet" href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#ffc107">
</head>

<body style="padding-top: 56px; padding-bottom: 56px;" class="auth-page">

    <?= $this->include('partials/header') ?>

    <div class="container">
        <div class="row h-100 justify-content-center">
            <!-- Die Spalte passt sich an: auf xs 12, sm 8, md 6, lg 4 -->
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">

                <?= $this->renderSection('main') ?>

            </div>
        </div>

        <!-- <div class="row">
            <div class="col-12">
                <div class="auth-promo-link text-center text-light mt-4">
                    <h5>Was ist Wurstify!</h5>
                    <p>Aus einer Leidenschaft für die perfekte Bratwurst geboren.<br>Erfahre hier mehr über uns und die App.</p>
                    <a href="https://wurstify.com" class="btn btn-lg btn-secondary">Mehr erfahren</a>
                </div>
            </div>
        </div> -->
    </div>

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

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= base_url('sw.js') ?>')
                    .then(registration => {
                        console.log('Service Worker registriert:', registration);
                    })
                    .catch(error => {
                        console.log('Service Worker Registrierung fehlgeschlagen:', error);
                    });
            });
        }
    </script>

</body>

</html>