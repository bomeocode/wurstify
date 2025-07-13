<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
<h1 class="h3 mb-4">Ansprüche auf Inhaberschaft</h1>

<div class="card">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Anbieter</th>
          <th>Antragsteller</th>
          <th>Status</th>
          <th>Datum</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($claims as $claim): ?>
          <tr>
            <td><?= $claim['id'] ?></td>
            <td><?= esc($claim['vendor_name']) ?></td>
            <td><?= esc($claim['username']) ?></td>
            <td>
              <span class="badge bg-<?= ($claim['status'] === 'pending') ? 'warning' : (($claim['status'] === 'approved') ? 'success' : 'danger') ?>">
                <?= esc($claim['status']) ?>
              </span>
            </td>
            <td><?= date('d.m.Y', strtotime($claim['created_at'])) ?></td>
            <td>
              <a href="<?= route_to('admin_claim_show', $claim['id']) ?>" class="btn btn-sm btn-primary">Prüfen</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?= $pager->links('default', 'wurstify_admin') ?>
  </div>
</div>
<?= $this->endSection() ?>