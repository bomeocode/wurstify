<h2>Inhaberschaft für "<?= esc($vendor['name']) ?>" beanspruchen</h2>
<hr>
<p>Um sicherzustellen, dass nur der rechtmäßige Besitzer diesen Eintrag verwalten kann, benötigen wir einen einfachen Nachweis von Ihnen.</p>

<div data-init-function="initializeClaimFormScripts">
  <?= form_open('claim/submit') ?>
  <input type="hidden" name="vendor_uuid" value="<?= esc($vendor['uuid']) ?>">

  <div class="mb-3">
    <label for="name" class="form-label">Ihr vollständiger Name</label>
    <input type="text" class="form-control" name="name" required>
  </div>
  <div class="mb-3">
    <label for="contact_email" class="form-label">Ihre Kontakt-E-Mail</label>
    <input type="email" class="form-control" name="contact_email" value="<?= auth()->user()->email ?? '' ?>" required>
  </div>
  <div class="mb-3">
    <label for="proof" class="form-label">Nachweis (z.B. Gewerbeschein-Nummer, Link zum Impressum)</label>
    <textarea name="proof" class="form-control" rows="4" required></textarea>
  </div>
  <div class="d-grid">
    <button type="submit" class="btn btn-primary">Anspruch senden</button>
  </div>
  <?= form_close() ?>
</div>