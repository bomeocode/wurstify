<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Layout bearbeiten: <?= esc($layout->name) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4">
  <h2 class="mb-3 mb-md-0 me-md-3">Layout bearbeiten: <em><?= esc($layout->name) ?></em></h2>
  <a href="<?= site_url('/layouts') ?>" class="btn btn-secondary flex-shrink-0">
    <i class="bi-arrow-left-circle me-2"></i>
    Zurück zur Übersicht
  </a>
</div>

<p>Vorlage: <strong><?= esc($layout->layout_template) ?></strong></p>
<hr>
<p>Klicken Sie auf einen Slot, um Inhalte zuzuweisen.</p>

<div class="layout-editor-container border p-2" data-layout-uuid="<?= esc($layout->uuid) ?>">
  <?php
  // Lade die passende Template-View und übergib die Slot-Daten
  echo $this->include('pages/layouts/templates/' . $layout->layout_template, ['slots' => $slots, 'layout' => $layout]);
  ?>
</div>

<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignmentModalLabel">Inhalt für Slot auswählen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Lade Medien...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>