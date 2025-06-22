<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Wurstify - Bewerten<?= $this->endSection() ?>

<?= $this->section('content') ?>
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

<?= form_open('ratings/create') ?>

<input type="hidden" name="latitude" id="latitude">
<input type="hidden" name="longitude" id="longitude">

<div id="location-section" class="mb-3">
  <p>Ermittle deinen Standort...</p>
  <button type="button" class="btn btn-secondary" id="skip-location">Standort manuell eingeben</button>
</div>

<div id="manual-address-section" class="mb-3" style="display: none;">
  <label for="address_manual" class="form-label">Adresse des Anbieters</label>
  <input type="text" class="form-control" value="<?= old('address_manual', '') ?>" name="address_manual" id="address_manual" placeholder="z.B. Musterstraße 1, 12345 Musterstadt">
</div>

<div class="mb-3">
  <label for="vendor_name" class="form-label">Name / Titel des Anbieters*</label>
  <input type="text" list="nearby-places" class="form-control" value="<?= old('vendor_name', '') ?>" name="vendor_name" id="vendor_name" required placeholder="z.B. Bratworscht-Express Cuxhaven">
  <datalist id="nearby-places"></datalist>
</div>

<?php
$categories = [
  'rating_appearance' => 'Aussehen',
  'rating_taste' => 'Geschmack',
  'rating_presentation' => 'Präsentation',
  'rating_price' => 'Preis/Leistung',
  'rating_service' => 'Personal/Service',
];
?>

<?php foreach ($categories as $field => $label): ?>
  <div class="mb-3">
    <label class="form-label"><?= $label ?>*</label>
    <div class="star-rating">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input
          type="radio"
          id="<?= esc($field) ?>-<?= $i ?>"
          name="<?= esc($field) ?>"
          value="<?= $i ?>"
          class="visually-hidden"
          <?php if ($i === 5) echo 'required'; // 'required' nur für einen Button in der Gruppe 
          ?>
          <?= old($field) == $i ? 'checked' : '' /* DIE MAGISCHE ZEILE */ ?>>
        <label for="<?= esc($field) ?>-<?= $i ?>">★</label>
      <?php endfor; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="mb-3">
  <label for="comment" class="form-label">Optionaler Kommentar</label>
  <textarea class="form-control" name="comment" id="comment" rows="3"><?= old('comment', '') ?></textarea>
</div>

<button type="submit" class="btn btn-primary">Bewertung absenden</button>
<?= form_close() ?>

<?= $this->endSection() ?>