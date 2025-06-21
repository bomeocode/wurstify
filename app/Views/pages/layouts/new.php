<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>EduScreen - Neues Layout<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h2>Neues Layout erstellen</h2>

<?= service('validation')->listErrors() ?>

<form action="<?= site_url('/layouts/create') ?>" method="post">
  <?= csrf_field() ?>

  <div class="mb-3">
    <label for="name" class="form-label">Name des Layouts</label>
    <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
    <small class="form-text text-muted">Geben Sie einen aussagekräftigen Namen ein, z.B. "Pausen-Anzeige Foyer".</small>
  </div>

  <div class="mb-3">
    <label for="layout_template" class="form-label">Layout-Vorlage auswählen</label>
    <select class="form-select" name="layout_template" id="layout_template" required>
      <option value="" disabled selected>Bitte wählen...</option>
      <?php foreach ($templates as $key => $label): ?>
        <option value="<?= esc($key) ?>" <?= old('layout_template') === $key ? 'selected' : '' ?>>
          <?= esc($label) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Layout erstellen und bearbeiten</button>
  <a href="<?= site_url('/layouts') ?>" class="btn btn-secondary">Abbrechen</a>
</form>

<?= $this->endSection() ?>