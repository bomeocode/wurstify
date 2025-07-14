<?= $this->extend('layouts/public_layout') ?>
<?= $this->section('title') ?>Bewerte <?= esc($vendor['name']) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  :root {
    --wurstify-primary: #A65E2E;
    --wurstify-dark: #3D2B1F;
  }

  /* Setzt den Farbverlauf als Seitenhintergrund */
  body {
    background: linear-gradient(160deg, var(--wurstify-dark) 0%, var(--wurstify-primary) 100%);
    color: #f8f9fa;
    /* Hellerer Text für besseren Kontrast */
  }

  .card {
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(5px);
  }

  /* Styling für die Sterne, jetzt mit besserem Umbruchverhalten */
  .star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    /* Sterne rechtsbündig */
    flex-wrap: nowrap;
    /* Verhindert unschönen Umbruch der Sterne */
  }

  .star-rating label {
    font-size: 2rem;
    /* Etwas kleiner für mobile Geräte */
    color: #ddd;
    cursor: pointer;
    padding: 0 0.1em;
    transition: color 0.2s;
  }

  .star-rating input:checked~label,
  .star-rating label:hover,
  .star-rating label:hover~label {
    color: #ffc107;
  }

  /* Titelbild des Anbieters */
  .vendor-header-qr {
    height: 150px;
    background-size: cover;
    background-position: center;
    border-top-left-radius: var(--bs-card-border-radius);
    border-top-right-radius: var(--bs-card-border-radius);
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
      <div class="card shadow-lg">
        <div class="vendor-header-qr" style="background-color: #6c757d; <?= $vendor['cover_image'] ? "background-image: url('/uploads/vendors/" . esc($vendor['cover_image'], 'attr') . "');" : '' ?>"></div>

        <div class="card-body p-4">
          <?php if (session('errors') !== null): ?>
            <div class="alert alert-danger" role="alert">
              <h4 class="alert-heading">Fehler!</h4>
              <p>Bitte überprüfen Sie Ihre Eingaben:</p>
              <hr>
              <?php foreach (session('errors') as $error): ?>
                <p class="mb-0"><?= esc($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="text-center mb-4">
            <?php if ($vendor['logo_image']): ?>
              <img src="/uploads/vendors/<?= esc($vendor['logo_image'], 'attr') ?>" class="rounded-circle mb-2" style="width:80px; height:80px; object-fit: cover; margin-top: -60px; border: 4px solid white;" alt="Logo">
            <?php endif; ?>
            <h1 class="h4 text-dark">Bewerte "<?= esc($vendor['name']) ?>"</h1>
          </div>

          <?= form_open('rate/' . $vendor['slug']) ?>
          <?= csrf_field() ?>
          <input type="hidden" name="qr_token" value="<?= esc($qr_token, 'attr') ?>">

          <div class="mb-3">
            <label for="qr_nickname" class="form-label text-dark">Dein Spitzname*</label>
            <input type="text" class="form-control" name="qr_nickname" id="qr_nickname" value="<?= old('qr_nickname') ?>" required>
          </div>

          <hr class="my-3">
          <h5 class="mb-3 text-dark">Deine Bewertung</h5>
          <?php
          $categories = ['rating_taste' => 'Geschmack', 'rating_appearance' => 'Aussehen', 'rating_presentation' => 'Präsentation', 'rating_price' => 'Preis/Leistung', 'rating_service' => 'Personal/Service'];
          ?>
          <?php foreach ($categories as $field => $label): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label text-dark mb-0"><?= $label ?>*</label>
              <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" id="<?= esc($field) ?>-<?= $i ?>" name="<?= esc($field) ?>" value="<?= $i ?>" class="visually-hidden" required />
                  <label for="<?= esc($field) ?>-<?= $i ?>">★</label>
                <?php endfor; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="mb-3 mt-3">
            <label for="comment" class="form-label text-dark">Optionaler Kommentar</label>
            <textarea class="form-control" name="comment" id="comment" rows="3"><?= old('comment') ?></textarea>
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">Bewertung absenden</button>
          </div>
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>