<?= $this->extend('layouts/admin_layout') ?>

<?= $this->section('main') ?>
<h1 class="h3 mb-4">Benutzer-Stufen verwalten</h1>

<div class="card">
  <div class="card-header">
    Alle Stufen
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Level</th>
            <th>Name</th>
            <th>Benötigte Bewertungen</th>
            <th>Aktion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($levels as $level): ?>
            <tr>
              <td><?= esc($level->level_number) ?></td>
              <td><?= esc($level->name) ?></td>
              <td><?= esc($level->min_ratings) ?></td>
              <td>
                <a href="<?= route_to('admin_level_edit', $level->id) ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>