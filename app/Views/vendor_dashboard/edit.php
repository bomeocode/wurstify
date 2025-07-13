<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Anbieterprofil bearbeiten<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
      <h1 class="h3 mb-4">Profil für "<?= esc($vendor['name']) ?>" bearbeiten</h1>

      <?= service('validation')->listErrors('list') ?>

      <?= form_open(route_to('vendor_update')) ?>

      <input type="hidden" name="logo_image" id="logo_image_filename" value="<?= esc($vendor['logo_image']) ?>">
      <input type="hidden" name="cover_image" id="cover_image_filename" value="<?= esc($vendor['cover_image']) ?>">

      <div class="card">
        <div class="card-body p-4">
          <div class="mb-3">
            <label for="name" class="form-label">Name Ihres Geschäfts</label>
            <input type="text" class="form-control" name="name" id="name" value="<?= old('name', $vendor['name']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Beschreibung</label>
            <textarea class="form-control" name="description" id="description" rows="4"><?= old('description', $vendor['description']) ?></textarea>
          </div>

          <hr class="my-4">
          <h5 class="mb-3">Bilder</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Logo (quadratisch)</label>
              <div id="logo-uploader" class="image-uploader-box">
                <img id="logo-preview" src="<?= $vendor['logo_image'] ? '/uploads/vendors/' . esc($vendor['logo_image'], 'attr') : '' ?>"
                  class="<?= !$vendor['logo_image'] ? 'd-none' : '' ?>">
                <span id="logo-placeholder" class="<?= $vendor['logo_image'] ? 'd-none' : '' ?>">Logo auswählen...</span>
              </div>
              <input type="file" id="logo-input" class="d-none" accept="image/png, image/jpeg, image/gif">
            </div>
            <div class="col-md-6">
              <label class="form-label">Titelbild (breit)</label>
              <div id="cover-uploader" class="image-uploader-box">
                <img id="cover-preview" src="<?= $vendor['cover_image'] ? '/uploads/vendors/' . esc($vendor['cover_image'], 'attr') : '' ?>"
                  class="<?= !$vendor['cover_image'] ? 'd-none' : '' ?>">
                <span id="cover-placeholder" class="<?= $vendor['cover_image'] ? 'd-none' : '' ?>">Titelbild auswählen...</span>
              </div>
              <input type="file" id="cover-input" class="d-none" accept="image/png, image/jpeg, image/gif">
            </div>
          </div>

          <hr class="my-4">
          <h5 class="mb-3">Öffnungszeiten</h5>
          <?php
          $days = ['monday' => 'Mo', 'tuesday' => 'Di', 'wednesday' => 'Mi', 'thursday' => 'Do', 'friday' => 'Fr', 'saturday' => 'Sa', 'sunday' => 'So'];
          ?>
          <?php foreach ($days as $key => $day): ?>
            <div class="input-group mb-2">
              <span class="input-group-text" style="width: 60px;"><?= $day ?></span>
              <input type="text" class="form-control" name="opening_hours[<?= $key ?>]" id="hours-<?= $key ?>" value="<?= old('opening_hours.' . $key, $opening_hours[$key] ?? '') ?>">
              <div class="input-group-text">
                <input class="form-check-input mt-0" type="checkbox" id="closed-<?= $key ?>" data-target-input="hours-<?= $key ?>" <?= (old('opening_hours.' . $key, $opening_hours[$key] ?? '') === 'geschlossen') ? 'checked' : '' ?>>
                <label class="form-check-label ms-2" for="closed-<?= $key ?>">Geschl.</label>
              </div>
            </div>
          <?php endforeach; ?>

          <hr class="my-4">
          <h5 class="mb-3">Links</h5>
          <div class="mb-3">
            <label for="website_url" class="form-label">Webseite</label>
            <input type="url" class="form-control" name="website_url" id="website_url" placeholder="https://..." value="<?= old('website_url', $vendor['website_url']) ?>">
          </div>
          <?php
          $socials = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'];
          $socialData = json_decode($vendor['social_media'], true) ?? [];
          ?>
          <div class="row">
            <?php foreach ($socials as $key => $label): ?>
              <div class="col-md-6 mb-3">
                <label for="social_<?= $key ?>" class="form-label"><?= $label ?></label>
                <div class="input-group"><span class="input-group-text">@</span><input type="text" class="form-control" name="social_media[<?= $key ?>]" id="social_<?= $key ?>" value="<?= old('social_media.' . $key, $socialData[$key] ?? '') ?>"></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <a href="<?= route_to('vendor_dashboard') ?>" class="btn btn-secondary me-2">Abbrechen</a>
            <button type="submit" class="btn btn-primary">Änderungen speichern</button>
          </div>
        </div>
      </div>

      <?= form_close() ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Logik für die "Geschlossen"-Checkboxen
    document.querySelectorAll('input[type="checkbox"][data-target-input]').forEach(box => {
      const targetInput = document.getElementById(box.dataset.targetInput);
      if (!targetInput) return;
      const updateInputState = () => {
        targetInput.readOnly = box.checked;
        if (box.checked) targetInput.value = 'geschlossen';
        else if (targetInput.value === 'geschlossen') targetInput.value = '';
      };
      box.addEventListener('change', updateInputState);
      updateInputState();
    });

    // Logik für die Bilder-Uploader
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