<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('main') ?>
<h1 class="h3 mb-4">Benutzer-Stufe bearbeiten</h1>

<?= service('validation')->listErrors() ?>

<div class="card">
  <div class="card-body">
    <?= form_open(route_to('admin_level_update', $level->id)) ?>

    <div class="mb-3">
      <label for="name" class="form-label">Name der Stufe</label>
      <input type="text" class="form-control" name="name" id="name" value="<?= old('name', $level->name) ?>" required>
    </div>

    <div class="mb-3">
      <label for="min_ratings" class="form-label">Mindestanzahl an Bewertungen</label>
      <input type="number" class="form-control" name="min_ratings" id="min_ratings" value="<?= old('min_ratings', $level->min_ratings) ?>" required>
      <small class="form-text text-muted">Die Anzahl an Bewertungen, die ein Nutzer benötigt, um diese Stufe zu erreichen.</small>
    </div>

    <div class="d-flex justify-content-end">
      <a href="<?= route_to('admin_levels_index') ?>" class="btn btn-secondary me-2">Abbrechen</a>
      <button type="submit" class="btn btn-primary">Speichern</button>
    </div>

    <?= form_close() ?>
  </div>
</div>
<?= $this->endSection() ?>