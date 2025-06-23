<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>Benutzer bearbeiten<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
  <h1 class="h3 mb-4">Benutzer "<?= esc($user->username) ?>" bearbeiten</h1>

  <?= form_open('admin/users/update/' . $user->id) ?>
  <div class="mb-3">
    <label class="form-label">E-Mail</label>
    <input type="email" class="form-control" value="<?= esc($user->email) ?>" disabled>
  </div>

  <div class="mb-3">
    <label for="group" class="form-label">Gruppe / Rolle</label>
    <select name="group" id="group" class="form-select">
      <?php foreach ($groups as $groupName => $groupInfo): ?>
        <option value="<?= esc($groupName) ?>" <?= ($groupName === $user->group) ? 'selected' : '' ?>>
          <?= esc($groupInfo['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-success">Speichern</button>
  <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">Abbrechen</a>
  </form>
</div>
<?= $this->endSection() ?>