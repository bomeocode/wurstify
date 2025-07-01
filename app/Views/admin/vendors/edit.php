<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Anbieter bearbeiten<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
  <h1 class="h3 mb-4">Anbieter "<?= esc($vendor['name']) ?>" bearbeiten</h1>

  <?= form_open('admin/vendors/update/' . $vendor['id']) ?>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="<?= old('name', $vendor['name']) ?>" required>
  </div>
  <div class="mb-3">
    <label for="address" class="form-label">Adresse</label>
    <input type="text" name="address" class="form-control" value="<?= old('address', $vendor['address']) ?>" required>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="latitude" class="form-label">Latitude</label>
      <input type="text" name="latitude" class="form-control" value="<?= old('latitude', $vendor['latitude']) ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label for="longitude" class="form-label">Longitude</label>
      <input type="text" name="longitude" class="form-control" value="<?= old('longitude', $vendor['longitude']) ?>" required>
    </div>
  </div>

  <button type="submit" class="btn btn-success">Änderungen speichern</button>
  <a href="<?= site_url('admin/vendors') ?>" class="btn btn-secondary">Abbrechen</a>
  </form>
</div>
<?= $this->endSection() ?>