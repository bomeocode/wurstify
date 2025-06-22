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
  <input type="text" class="form-control" name="address_manual" id="address_manual" placeholder="z.B. Musterstraße 1, 12345 Musterstadt">
</div>

<div class="mb-3">
  <label for="vendor_name" class="form-label">Name / Titel des Anbieters*</label>
  <input type="text" list="nearby-places" class="form-control" name="vendor_name" id="vendor_name" required placeholder="z.B. Bratworscht-Express Cuxhaven">
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
      <input type="radio" id="<?= $field ?>-5" name="<?= $field ?>" value="5" required class="visually-hidden" /><label for="<?= $field ?>-5">★</label>
      <input type="radio" id="<?= $field ?>-4" name="<?= $field ?>" value="4" class="visually-hidden" /><label for="<?= $field ?>-4">★</label>
      <input type="radio" id="<?= $field ?>-3" name="<?= $field ?>" value="3" class="visually-hidden" /><label for="<?= $field ?>-3">★</label>
      <input type="radio" id="<?= $field ?>-2" name="<?= $field ?>" value="2" class="visually-hidden" /><label for="<?= $field ?>-2">★</label>
      <input type="radio" id="<?= $field ?>-1" name="<?= $field ?>" value="1" class="visually-hidden" /><label for="<?= $field ?>-1">★</label>
    </div>
  </div>
<?php endforeach; ?>

<div class="mb-3">
  <label for="comment" class="form-label">Optionaler Kommentar</label>
  <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
</div>

<button type="submit" class="btn btn-primary">Bewertung absenden</button>
<?= form_close() ?>

<?= $this->endSection() ?>