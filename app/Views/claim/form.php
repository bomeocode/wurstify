<h2>Inhaberschaft für "<?= esc($vendor['name']) ?>" beanspruchen</h2>
<hr>
<p><strong>Um sicherzustellen, dass nur der rechtmäßige Besitzer diesen Eintrag verwalten kann, benötigen wir einen einfachen Nachweis von Ihnen. Wir werden diesbezüglich Kontakt mit Ihnen aufnehmen.</strong></p>

<div data-init-function="initializeClaimFormScripts">
  <?= form_open('claim/submit') ?>
  <input type="hidden" id="vendor_uuid" name="vendor_uuid" value="<?= esc($vendor['uuid']) ?>">

  <div class="mb-3">
    <label for="claimant_name" class="form-label">Ihr vollständiger Name</label>
    <input type="text" class="form-control" id="claimant_name" name="claimant_name" required>
  </div>
  <div class="mb-3">
    <label for="contact_email" class="form-label">Ihre Kontakt-E-Mail</label>
    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= auth()->user()->email ?? '' ?>" required>
  </div>
  <div class="mb-3">
    <label for="proof_text" class="form-label">Ihre Nachricht / Weitere Kontaktdaten</label>
    <textarea class="form-control" id="proof_text" name="proof_text" rows="4" required></textarea>
  </div>
  <div class="d-grid">
    <button type="submit" class="btn btn-primary">Anspruch senden</button>
  </div>
  <?= form_close() ?>
</div>