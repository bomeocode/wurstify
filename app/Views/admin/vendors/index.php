<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Anbieter-Verwaltung</h1>
    <a href="<?= route_to('admin_dashboard') ?>" class="btn btn-outline-secondary">
      Zurück zum Dashboard
    </a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('admin/vendors') ?>" method="get" class="d-flex" id="vendor-search-form">
        <input type="search" name="q" class="form-control me-2" placeholder="Suche nach Name oder Adresse..." value="<?= esc($searchTerm ?? '', 'attr') ?>" id="vendor-search-input">
        <button type="submit" class="btn btn-primary">Suchen</button>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Adresse</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vendors as $vendor): ?>
          <tr>
            <td><?= $vendor['id'] ?></td>
            <td><?= esc($vendor['name']) ?></td>
            <td><?= esc($vendor['address']) ?></td>
            <td>
              <a href="<?= site_url('admin/vendors/edit/' . $vendor['id']) ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
              <a href="<?= site_url('admin/vendors/delete/' . $vendor['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sind Sie sicher?')">Löschen</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-center">
    <?= $pager->links('default', 'wurstify_admin') ?>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('vendor-search-input');
    const searchForm = document.getElementById('vendor-search-form');

    // Stellen sicher, dass die Elemente existieren, bevor wir den Listener hinzufügen
    if (searchInput && searchForm) {

      // Das 'search'-Event wird ausgelöst, wenn der User 'Enter' drückt
      // ODER wenn er auf das kleine 'x' im Suchfeld klickt.
      searchInput.addEventListener('search', function() {

        // Wir prüfen, ob das Feld nach dem Event leer ist.
        // Das ist der Fall, wenn das 'x' geklickt wurde.
        if (searchInput.value === '') {

          // Wenn ja, leiten wir zur Basis-URL des Formulars weiter,
          // was die Seite ohne Suchparameter neu lädt.
          window.location.href = searchForm.action;
        }
      });
    }
  });
</script>
<?= $this->endSection() ?>