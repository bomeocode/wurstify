<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Bewertungs-Verwaltung<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
  <h1 class="h3 mb-4">Bewertungs-Verwaltung</h1>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Anbieter</th>
          <th>Benutzer</th>
          <th>Kommentar (Auszug)</th>
          <th>Datum</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ratings as $rating): ?>
          <tr>
            <td><?= $rating['id'] ?></td>
            <td><?= esc($rating['vendor_name']) ?></td>
            <td><?= esc($rating['username']) ?></td>
            <td><?= esc(character_limiter($rating['comment'], 50)) ?></td>
            <td><?= date('d.m.Y', strtotime($rating['created_at'])) ?></td>
            <td>
              <a href="<?= site_url('admin/ratings/edit/' . $rating['id']) ?>" class="btn btn-sm btn-primary">Bearbeiten</a>
              <a href="<?= site_url('admin/ratings/delete/' . $rating['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sind Sie sicher?')">Löschen</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?= $pager->links('default', 'wurstify_admin') ?>
</div>
<?= $this->endSection() ?>