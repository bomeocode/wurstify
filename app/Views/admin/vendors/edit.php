<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
<style>
  .image-uploader-box {
    border: 2px dashed #ccc;
    padding: 1rem;
    border-radius: .5rem;
    text-align: center;
    cursor: pointer;
    background-color: #f8f9fa;
  }

  .image-uploader-box:hover {
    border-color: #0d6efd;
  }

  .image-uploader-box img {
    max-height: 150px;
    width: auto;
  }
</style>
<div class="container mt-4">
  <h1 class="h3 mb-4">Anbieter "<?= esc($vendor['name']) ?>" bearbeiten</h1>

  <?= form_open('admin/vendors/update/' . $vendor['id']) ?>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="<?= old('name', $vendor['name']) ?>" required>
  </div>
  <div class="mb-3">
    <label for="address" class="form-label">Adresse</label>
    <input type="text" name="address" class="form-control" value="<?= old('address', $vendor['address']) ?>" required>
  </div>
  <div class="mb-3">
    <label for="description" class="form-label">Beschreibung</label>
    <textarea class="form-control" name="description" id="description" rows="4"><?= old('description', $vendor['description']) ?></textarea>
    <small class="form-text text-muted">Eine kurze, ansprechende Beschreibung des Imbisses.</small>
  </div>

  <hr class="my-4">
  <h5 class="mb-3">Öffnungszeiten</h5>

  <?php
  // Wir definieren die Wochentage
  $days = [
    'monday'    => 'Montag',
    'tuesday'   => 'Dienstag',
    'wednesday' => 'Mittwoch',
    'thursday'  => 'Donnerstag',
    'friday'    => 'Freitag',
    'saturday'  => 'Samstag',
    'sunday'    => 'Sonntag',
  ];
  ?>

  <?php foreach ($days as $key => $day): ?>
    <div class="input-group mb-2">
      <span class="input-group-text" style="width: 120px;"><?= $day ?></span>
      <input type="text" class="form-control" name="opening_hours[<?= $key ?>]"
        id="hours-<?= $key ?>"
        placeholder="z.B. 09:00 - 18:00"
        value="<?= old('opening_hours.' . $key, $opening_hours[$key] ?? '') ?>">
      <div class="input-group-text">
        <input class="form-check-input mt-0" type="checkbox"
          id="closed-<?= $key ?>"
          data-target-input="hours-<?= $key ?>"
          <?php
          $value = old('opening_hours.' . $key, $opening_hours[$key] ?? '');
          if (trim(strtolower($value)) === 'geschlossen') {
            echo 'checked';
          }
          ?>>
        <label class="form-check-label ms-2" for="closed-<?= $key ?>">Geschlossen</label>
      </div>
    </div>
  <?php endforeach; ?>

  <script>

  </script>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="website_url" class="form-label">Webseite</label>
      <input type="url" class="form-control" name="website_url" id="website_url" placeholder="https://..." value="<?= old('website_url', $vendor['website_url']) ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label for="category" class="form-label">Kategorie</label>
      <select name="category" id="category" class="form-select">
        <option value="stationär" <?= old('category', $vendor['category']) === 'stationär' ? 'selected' : '' ?>>Stationär</option>
        <option value="mobil" <?= old('category', $vendor['category']) === 'mobil' ? 'selected' : '' ?>>Mobil</option>
      </select>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="latitude" class="form-label">Latitude</label>
      <input type="text" name="latitude" class="form-control" value="<?= old('latitude', $vendor['latitude']) ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label for="longitude" class="form-label">Longitude</label>
      <input type="text" name="longitude" class="form-control" value="<?= old('longitude', $vendor['longitude']) ?>" required>
    </div>
  </div>

  <hr class="my-4">
  <h5 class="mb-3">Social-Media-Profile</h5>

  <?php
  // Wir definieren die Social-Media-Kanäle
  $socials = [
    'facebook'  => 'Facebook',
    'instagram' => 'Instagram',
    'tiktok'    => 'TikTok',
    'youtube'   => 'YouTube',
  ];
  // Wir dekodieren die JSON-Daten aus der Datenbank
  $socialData = json_decode($vendor['social_media'], true) ?? [];
  ?>

  <div class="row">
    <?php foreach ($socials as $key => $label): ?>
      <div class="col-md-6 mb-3">
        <label for="social_<?= $key ?>" class="form-label"><?= $label ?></label>
        <div class="input-group">
          <span class="input-group-text">@</span>
          <input type="text" class="form-control"
            name="social_media[<?= $key ?>]"
            id="social_<?= $key ?>"
            placeholder="Benutzername"
            value="<?= old('social_media.' . $key, $socialData[$key] ?? '') ?>">
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <input type="hidden" name="logo_image" id="logo_image_filename" value="<?= esc($vendor['logo_image']) ?>">
  <input type="hidden" name="cover_image" id="cover_image_filename" value="<?= esc($vendor['cover_image']) ?>">

  <div class="card mb-3">
    <div class="card-body">
      <hr class="my-4">
      <h5 class="mb-3">Bilder</h5>
      <div class="row">
        <div class="col-md-6">
          <label class="form-label">Logo (quadratisch)</label>
          <div id="logo-uploader" class="image-uploader-box">
            <img id="logo-preview" src="<?= $vendor['logo_image'] ? '/uploads/vendors/' . esc($vendor['logo_image'], 'attr') : '' ?>"
              class="<?= !$vendor['logo_image'] ? 'd-none' : '' ?>">
            <span id="logo-placeholder" class="<?= $vendor['logo_image'] ? 'd-none' : '' ?>">Logo auswählen...</span>
          </div>
          <input type="file" id="logo-input" class="d-none" accept="image/*">
        </div>
        <div class="col-md-6">
          <label class="form-label">Titelbild (breit)</label>
          <div id="cover-uploader" class="image-uploader-box">
            <img id="cover-preview" src="<?= $vendor['cover_image'] ? '/uploads/vendors/' . esc($vendor['cover_image'], 'attr') : '' ?>"
              class="<?= !$vendor['cover_image'] ? 'd-none' : '' ?>">
            <span id="cover-placeholder" class="<?= $vendor['cover_image'] ? 'd-none' : '' ?>">Titelbild auswählen...</span>
          </div>
          <input type="file" id="cover-input" class="d-none" accept="image/*">
        </div>
      </div>

    </div>
  </div>

  <button type="submit" class="btn btn-success">Änderungen speichern</button>
  <a href="<?= site_url('admin/vendors') ?>" class="btn btn-secondary">Abbrechen</a>
  </form>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    const checkboxes = document.querySelectorAll('input[type="checkbox"][data-target-input]');

    checkboxes.forEach(box => {
      const targetInput = document.getElementById(box.dataset.targetInput);
      if (!targetInput) return;

      // Funktion zum Aktualisieren des Feldes
      const updateInputState = () => {
        if (box.checked) {
          targetInput.value = 'geschlossen';
          // NEU: 'readOnly' statt 'disabled' verwenden
          targetInput.readOnly = true;
        } else {
          if (targetInput.value === 'geschlossen') {
            targetInput.value = '';
          }
          // NEU: 'readOnly' statt 'disabled' verwenden
          targetInput.readOnly = false;
        }
      };

      // Listener für Klicks auf die Checkbox
      box.addEventListener('change', updateInputState);

      // Initialen Zustand beim Laden der Seite prüfen
      updateInputState();
    });

    function setupUploader(type) {
      const uploaderBox = document.getElementById(`${type}-uploader`);
      const fileInput = document.getElementById(`${type}-input`);
      const preview = document.getElementById(`${type}-preview`);
      const placeholder = document.getElementById(`${type}-placeholder`);
      const hiddenInput = document.getElementById(`${type}_image_filename`);

      if (!uploaderBox || !fileInput) return;

      // Klick auf die Box öffnet den Datei-Dialog
      uploaderBox.addEventListener('click', () => fileInput.click());

      // Wenn eine Datei ausgewählt wird
      fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
          const file = fileInput.files[0];

          // Lokale Vorschau
          const reader = new FileReader();
          reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
          };
          reader.readAsDataURL(file);

          // AJAX Upload
          uploadImage(file, hiddenInput, preview);
        }
      });
    }

    function uploadImage(file, hiddenInput, preview) {
      const formData = new FormData();
      formData.append('image', file);
      const csrfInput = document.querySelector('form input[name^="csrf_"]');
      formData.append(csrfInput.name, csrfInput.value);

      // Visuelles Feedback (z.B. Rand animieren)
      preview.style.border = '2px solid #0d6efd';

      fetch("<?= route_to('admin_vendor_image_upload') ?>", {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => {
          if (!response.ok) {
            // Wenn die Antwort nicht OK ist, versuchen wir, den Fehlertext zu bekommen
            return response.json().then(err => {
              throw new Error(err.error || 'Unbekannter Fehler')
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.filename) {
            hiddenInput.value = data.filename;
            preview.src = '/uploads/vendors/' + data.filename; // Pfad zur Server-Version aktualisieren
          }
        })
        .catch(error => {
          alert(`Upload fehlgeschlagen: ${error.message}`);
          preview.src = ''; // Vorschau bei Fehler entfernen
          preview.classList.add('d-none');
          document.getElementById(preview.id.replace('-preview', '-placeholder')).classList.remove('d-none');
        })
        .finally(() => {
          preview.style.border = 'none';
        });
    }

    // Initialisiere beide Uploader
    setupUploader('logo');
    setupUploader('cover');

  });
</script>
<?= $this->endSection() ?>