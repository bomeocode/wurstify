<style>
  .star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
  }

  .star-rating label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
  }

  .star-rating input:checked~label,
  .star-rating label:hover,
  .star-rating label:hover~label {
    color: #ffc107;
  }
</style>
<h2>Neue Bratwurst-Bewertung</h2>

<?= form_open(site_url('ratings/create'), ['id' => 'rating-form-in-modal']) ?>
<input type="hidden" name="vendor_id" value="<?= esc($vendor['id'] ?? '') ?>">

<div class="mb-3">
  <label for="address_manual" class="form-label">Adresse des Anbieters</label>
  <input type="text" class="form-control" name="address_manual" id="address_manual" value="<?= old('address_manual', $vendor['address'] ?? '') ?>" <?= isset($vendor) ? 'readonly' : '' ?>>
  <button class="btn btn-outline-secondary w-100 mt-2" type="button" id="use-current-location" <?= isset($vendor) ? 'disabled' : '' ?>>Meinen Standort verwenden</button>
  <div id="vendor-suggestions" class="list-group mt-2"></div>
</div>

<div class="mb-3">
  <label for="vendor_name" class="form-label">Name des Anbieters*</label>
  <input type="text" class="form-control" name="vendor_name" id="vendor_name" required value="<?= old('vendor_name', $vendor['name'] ?? '') ?>" <?= isset($vendor) ? 'readonly' : '' ?>>
</div>
<?php if (!isset($vendor)): ?>
  <div class="mb-4" id="vendor-category-block">
    <label class="form-label d-block">Art des Anbieters*</label>
    <div class="btn-group" role="group">
      <input type="radio" class="btn-check" name="vendor_category" id="category-stationaer" value="stationär" autocomplete="off" checked>
      <label class="btn btn-outline-primary" for="category-stationaer">Stationär (Fester Standort)</label>

      <input type="radio" class="btn-check" name="vendor_category" id="category-mobil" value="mobil" autocomplete="off">
      <label class="btn btn-outline-primary" for="category-mobil">Mobil / Event</label>
    </div>
  </div>
<?php endif; ?>
<hr>
<h5 class="mt-4">Ihre Bewertung</h5>

<?php $categories = ['rating_appearance' => 'Aussehen', 'rating_taste' => 'Geschmack', 'rating_presentation' => 'Präsentation', 'rating_price' => 'Preis/Leistung', 'rating_service' => 'Personal/Service',]; ?>
<?php foreach ($categories as $field => $label): ?>
  <div class="mb-3">
    <label class="form-label d-block"><?= $label ?>*</label>
    <div class="star-rating">
      <?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" id="<?= esc($field) ?>-<?= $i ?>" name="<?= esc($field) ?>" value="<?= $i ?>" class="visually-hidden" required /><label for="<?= esc($field) ?>-<?= $i ?>">★</label><?php endfor; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="mb-3">
  <label class="form-label">Bilder hinzufügen (bis zu 3)</label>
  <div class="row g-3">
    <?php for ($i = 1; $i <= 3; $i++): ?>
      <div class="col-4">
        <div class="rating-image-upload-wrap" data-slot="<?= $i ?>">
          <input class="rating-file-upload-input" type='file' accept="image/*" />
          <input type="hidden" name="image<?= $i ?>" id="image<?= $i ?>_filename">

          <div class="rating-drag-text">
            <div class="rating-upload-icon">+</div>
          </div>

          <div class="rating-file-upload-content">
            <img class="rating-file-upload-image" src="#" alt="Vorschau" />

            <div class="rating-progress-bar-wrap">
              <div class="rating-progress-bar"></div>
            </div>

            <div class="rating-image-title-wrap">
              <button type="button" class="rating-remove-image">Entfernen</button>
            </div>
          </div>
        </div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<div class="mb-3"><label for="comment" class="form-label">Optionaler Kommentar</label><textarea class="form-control" name="comment" id="comment" rows="3"></textarea></div>
<div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">Bewertung speichern</button></div>

<?= form_close() ?>