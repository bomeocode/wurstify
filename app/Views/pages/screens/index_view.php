<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>EduScreen - Bildschirm-Verwaltung<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4">
  <h2 class="mb-3 mb-md-0">Bildschirm-Verwaltung</h2>

  <div class="d-grid gap-2 d-md-flex justify-content-md-end">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#groupsModal">
      <i class="bi-collection-fill me-2"></i> Gruppen verwalten
    </button>
    <a href="<?= site_url('/screens/new') ?>" class="btn btn-primary">
      <i class="bi-plus-lg"></i> Neuen Bildschirm anlegen
    </a>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Name</th>
        <th>Standort</th>
        <th>Gruppe</th>
        <th>Aktionen</th>
      </tr>
    </thead>
    <tbody>
      <?php if (! empty($screens)): ?>
        <?php foreach ($screens as $screen): ?>
          <tr>
            <td><strong><?= esc($screen->name) ?></strong></td>
            <td><?= esc($screen->location) ?></td>
            <td>
              <?php if ($screen->group_name): ?>
                <span class="badge bg-info"><?= esc($screen->group_name) ?></span>
              <?php else: ?>
                <span class="text-muted">Keine</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="<?= site_url('/display/' . $screen->uuid) ?>" class="btn btn-outline-success btn-sm" target="_blank" title="Öffentliche Anzeige-URL" data-bs-toggle="tooltip"><i class="bi-display-fill"></i></a>
                <a href="<?= site_url('/screens/edit/' . $screen->uuid) ?>" class="btn btn-outline-secondary btn-sm" title="Bildschirm bearbeiten" data-bs-toggle="tooltip"><i class="bi-pencil-fill"></i></a>
                <a href="<?= site_url('/screens/delete/' . $screen->uuid) ?>" class="btn btn-outline-danger btn-sm" title="Bildschirm löschen" data-bs-toggle="tooltip" onclick="return confirm('Sind Sie sicher?')"><i class="bi-trash-fill"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center">Sie haben noch keine Bildschirme angelegt.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>


<div class="modal fade" id="groupsModal" tabindex="-1" aria-labelledby="groupsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="groupsModalLabel">Bildschirmgruppen verwalten</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Hier wird bald die Verwaltung für die Gruppen erscheinen...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>


<?= $this->endSection() ?>