<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Benutzerverwaltung<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">

    <h1 class="h3 mb-0">Benutzerverwaltung</h1>

    <a href="<?= route_to('admin_dashboard') ?>" class="btn btn-outline-secondary">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z" />
      </svg>
      Zurück zum Dashboard
    </a>

  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form action="<?= site_url('admin/users') ?>" method="get" class="d-flex" id="user-search-form">
        <input
          type="search"
          name="q"
          class="form-control me-2"
          placeholder="Suche nach Username oder E-Mail..."
          value="<?= esc($searchTerm ?? '', 'attr') ?>"
          id="user-search-input">
        <button type="submit" class="btn btn-primary">Suchen</button>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>E-Mail</th>
          <th>Gruppe/Rolle</th>
          <th>Registriert am</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= esc($user->id) ?></td>
              <td><?= esc($user->username) ?></td>
              <td><?= esc($user->email) ?></td>
              <td>
                <?php
                $groups = is_array($user->groups) ? $user->groups : explode(', ', (string) $user->groups);
                ?>
                <?php if (!empty($user->groups)): ?>
                  <?php foreach ($groups as $group): ?>
                    <span class="badge <?= (trim($group) === 'admin' || trim($group) === 'superadmin') ? 'bg-danger' : 'bg-secondary' ?>">
                      <?= esc(trim($group)) ?>
                    </span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="badge bg-light text-dark">Keine</span>
                <?php endif; ?>
              </td>
              <td><?= $user->created_at->format('d.m.Y H:i') ?></td>
              <td>
                <a href="<?= site_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
                <a href="<?= site_url('admin/users/delete/' . $user->id) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sind Sie sicher?')">Löschen</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center">Keine Benutzer für die aktuellen Kriterien gefunden.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-center">
    <?php if ($pager) : ?>
      <?= $pager->links('default', 'wurstify_admin') ?>
    <?php endif ?>
  </div>

</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search-input');
    const searchForm = document.getElementById('user-search-form');

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