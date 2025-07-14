<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Wurstify - Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h1>Hallo <?= esc($user->username) ?>!</h1>

<div id="map-container" data-ratings="<?= esc(json_encode($vendors)) ?>">
  <div id="map"></div>
</div>

<button type="button" class="fab-help open-modal" data-url="<?= site_url('help/guide') ?>" title="Anleitung anzeigen">
  <i class="bi bi-question-lg"></i>
</button>

<div id="tooltip"></div>

<button type="button" class="fab open-modal" data-url="<?= site_url('ratings/new') ?>" title="Neue Bewertung hinzufügen">
  <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z" />
  </svg>
</button>

<?= $this->endSection() ?>