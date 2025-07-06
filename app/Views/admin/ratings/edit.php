<?= $this->extend('layouts/admin_layout') ?>
<?= $this->section('main') ?>
<div class="container mt-4">
  <h1 class="h3 mb-4">Bewertung #<?= $rating['id'] ?> bearbeiten</h1>

  <?= form_open('admin/ratings/update/' . $rating['id']) ?>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Anbieter</label>
      <input type="text" class="form-control" value="<?= esc($rating['vendor_name']) ?>" disabled>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Benutzer</label>
      <input type="text" class="form-control" value="<?= esc($rating['username']) ?>" disabled>
    </div>
  </div>

  <div class="mb-3">
    <label for="comment" class="form-label">Kommentar</label>
    <textarea name="comment" class="form-control" rows="4"><?= esc($rating['comment']) ?></textarea>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3"><label for="image1" class="form-label">Bild 1</label><input type="text" name="image1" class="form-control" value="<?= esc($rating['image1']) ?>"></div>
    <div class="col-md-4 mb-3"><label for="image2" class="form-label">Bild 2</label><input type="text" name="image2" class="form-control" value="<?= esc($rating['image2']) ?>"></div>
    <div class="col-md-4 mb-3"><label for="image3" class="form-label">Bild 3</label><input type="text" name="image3" class="form-control" value="<?= esc($rating['image3']) ?>"></div>
  </div>

  <button type="submit" class="btn btn-success">Änderungen speichern</button>
  <a href="<?= site_url('admin/ratings') ?>" class="btn btn-secondary">Abbrechen</a>
  </form>
</div>
<?= $this->endSection() ?>