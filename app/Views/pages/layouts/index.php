<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>EduScreen - Layouts<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4">
  <h2 class="mb-3 mb-md-0 me-md-3">Layout-Verwaltung</h2>
  <a href="<?= site_url('/layouts/new') ?>" class="btn btn-primary flex-shrink-0">
    <i class="bi-plus-lg"></i> Neues Layout erstellen
  </a>
</div>

<div class="list-group">
  <?php if (! empty($layouts)): ?>
    <?php foreach ($layouts as $layout): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?= esc($layout->name) ?></strong>
          <small class="text-muted d-block">Vorlage: <?= esc($layout->layout_template) ?></small>
        </div>
        <div class="btn-group" role="group">
          <a href="<?= site_url('layouts/display/' . $layout->uuid) ?>" class="btn btn-outline-success btn-sm" target="_blank" title="Anzeige-Vorschau"><i class="bi-display-fill"></i></a>
          <a href="<?= site_url('layouts/edit/' . $layout->uuid) ?>" class="btn btn-outline-secondary btn-sm" title="Layout bearbeiten"><i class="bi-pencil-fill"></i></a>
          <button type="submit" class="btn btn-outline-danger btn-sm"
            form="delete-form-<?= $layout->uuid ?>"
            title="Layout löschen"
            data-bs-toggle="tooltip">
            <i class="bi-trash-fill"></i>
          </button>
        </div>
        <form action="<?= site_url('layouts/delete/' . $layout->uuid) ?>"
          method="post"
          id="delete-form-<?= $layout->uuid ?>"
          class="d-none"
          onsubmit="return confirm('Sind Sie sicher, dass Sie das Layout \'<?= addslashes(esc($layout->name)) ?>\' endgültig löschen möchten?')">
          <?= csrf_field() ?>
        </form>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Sie haben noch keine Layouts erstellt. <a href="<?= site_url('/layouts/new') ?>">Jetzt das erste Layout erstellen.</a></p>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>