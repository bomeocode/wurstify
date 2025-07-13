<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
<h1 class="h3 mb-4">Anspruch #<?= $claim['id'] ?> prüfen</h1>

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">Details des Anspruchs</div>
      <div class="card-body">
        <dl class="row">
          <dt class="col-sm-4">Antragsteller</dt>
          <dd class="col-sm-8"><?= esc($claim['username']) ?> (ID: <?= esc($claim['user_id']) ?>)</dd>

          <dt class="col-sm-4">Anbieter</dt>
          <dd class="col-sm-8"><?= esc($claim['vendor_name']) ?> (ID: <?= esc($claim['vendor_id']) ?>)</dd>

          <dt class="col-sm-4">Kontakt-E-Mail</dt>
          <dd class="col-sm-8"><?= esc($claim['contact_email']) ?></dd>

          <dt class="col-sm-4">Nachweis / Begründung</dt>
          <dd class="col-sm-8">
            <p class="border p-2 rounded bg-light"><em><?= nl2br(esc($claim['proof_text'])) ?></em></p>
          </dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card">
      <div class="card-header">Aktion durchführen</div>
      <div class="card-body">
        <p>Prüfen Sie den Anspruch sorgfältig. Eine Genehmigung kann nicht rückgängig gemacht werden.</p>
        <?= form_open(route_to('admin_claim_process')) ?>
        <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
        <div class="d-grid gap-2">
          <button type="submit" name="action" value="approve" class="btn btn-success" <?= $claim['status'] !== 'pending' ? 'disabled' : '' ?>>
            Anspruch genehmigen & Inhaberschaft übertragen
          </button>
          <button type="submit" name="action" value="reject" class="btn btn-danger" <?= $claim['status'] !== 'pending' ? 'disabled' : '' ?>>
            Anspruch ablehnen
          </button>
          <a href="<?= route_to('admin_claims') ?>" class="btn btn-secondary mt-2">Zurück zur Übersicht</a>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>