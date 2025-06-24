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

<div id="manual-address-section" class="mb-3">
  <label for="address_manual" class="form-label">Adresse des Anbieters</label>
  <div class="input-group">
    <input type="text" class="form-control" name="address_manual" id="address_manual" placeholder="z.B. Musterstraße 1, 12345 Musterstadt">
    <button class="btn btn-outline-secondary" type="button" id="use-current-location" title="Meinen aktuellen Standort verwenden">
      <svg width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z" />
        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
      </svg>
    </button>
  </div>
  <div class="form-text" id="location-status"></div>
  <div id="vendor-suggestions" class="list-group mt-2">
  </div>
</div>

<div class="mb-3">
  <label for="vendor_name" class="form-label">Name / Titel des Anbieters*</label>
  <input type="text" class="form-control" name="vendor_name" id="vendor_name" required placeholder="z.B. Bratworscht-Express Cuxhaven">
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