<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Profil bearbeiten<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5">
  <h1 class="mb-4">Profil bearbeiten</h1>

  <?php if (session()->has('message')) : ?>
    <div class="alert alert-success"><?= session('message') ?></div>
  <?php endif ?>

  <div class="row gx-5">

    <div class="col-md-4">
      <h4>Profilbild</h4>

      <img src="<?= $user->avatar ? '/uploads/avatars/' . esc($user->avatar, 'attr') : '/assets/img/avatar-placeholder.png' ?>"
        id="avatar-preview"
        class="img-fluid rounded mb-2 shadow-sm"
        alt="Profilbild"
        style="width: 100%; aspect-ratio: 1/1; object-fit: cover; background-color: #f8f9fa;">

      <div class="progress mb-2" id="progress-wrap" style="display: none;">
        <div id="progress-bar" class="progress-bar" role="progressbar"></div>
      </div>

      <div class="btn-group w-100 mb-3" role="group">
        <label for="avatar-input" class="btn btn-outline-secondary">Bild ändern</label>
        <button type="button" id="remove-avatar-btn" class="btn btn-outline-secondary">Entfernen</button>
      </div>

      <input type="file" id="avatar-input" accept="image/*" class="d-none">
    </div>

    <div class="col-md-8">
      <?= form_open(route_to('profile_update_details')) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="avatar" id="avatar-filename-input" value="<?= esc($user->avatar) ?>">

      <h4>Kontodetails</h4>

      <div class="mb-3">
        <label for="email" class="form-label">E-Mail-Adresse</label>
        <input type="email" class="form-control" value="<?= esc(auth()->user()->getIdentities()[0]->secret ?? '') ?>" disabled>
      </div>
      <div class="mb-3">
        <label for="username" class="form-label">Benutzername</label>
        <input type="text" name="username" class="form-control" value="<?= old('username', $user->username) ?>">
      </div>
      <button type="submit" id="save-details-btn" class="btn btn-primary">Details speichern</button>
      <?= form_close() ?>

      <hr class="my-4">

      <h4>Passwort ändern</h4>
      <?= form_open(route_to('profile_update_password')) ?>
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label">Altes Passwort</label>
        <input type="password" name="old_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Neues Passwort</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Neues Passwort bestätigen</label>
        <input type="password" name="password_confirm" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Passwort ändern</button>
      <?= form_close() ?>
    </div>

  </div>
</div>
<?= $this->endSection() ?>