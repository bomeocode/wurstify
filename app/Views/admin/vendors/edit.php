<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
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
    });
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

  <button type="submit" class="btn btn-success">Änderungen speichern</button>
  <a href="<?= site_url('admin/vendors') ?>" class="btn btn-secondary">Abbrechen</a>
  </form>
</div>
<?= $this->endSection() ?>