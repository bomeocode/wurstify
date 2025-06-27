<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Profil bearbeiten<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-5">
  <h1>Profil bearbeiten</h1>
  <p>Hier können Sie Ihre Kontoinformationen anpassen.</p>

  <?php if (session()->has('message')) : ?>
    <div class="alert alert-success"><?= session('message') ?></div>
  <?php endif ?>
  <?php if (session()->has('errors')) : ?>
    <ul class="alert alert-danger">
      <?php foreach (session('errors') as $error) : ?>
        <li><?= $error ?></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>

  <div class="row">
    <div class="col-md-4">
      <h4>Profilbild</h4>
      <div class="image-upload-wrap">
        <input class="file-upload-input" type='file' accept="image/*" />
        <div class="drag-text">
          <div class="upload-icon">+</div>
        </div>
        <div class="file-upload-content">
          <img class="file-upload-image" src="<?= $user->avatar ? '/uploads/avatars/' . esc($user->avatar, 'attr') : '#' ?>" alt="Profilbild" />
          <div class="progress-bar-wrap">
            <div class="progress-bar"></div>
          </div>
        </div>
      </div>
      <p class="text-center mt-2">
        <button type="button" class="btn btn-sm btn-outline-danger remove-image">Bild entfernen</button>
      </p>
    </div>

    <div class="col-md-8">
      <h4>Kontodetails</h4>
      <?= form_open('profile/update') ?>
      <input type="hidden" class="csrf-token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
      <input type="hidden" name="avatar" id="image1_filename" value="<?= esc($user->avatar) ?>">

      <div class="mb-3">
        <label for="email" class="form-label">E-Mail-Adresse</label>
        <input type="email" id="email" class="form-control" value="<?= esc($user->getIdentities()[0]->secret ?? '') ?>" disabled>
        <div class="form-text">Die E-Mail-Adresse kann nicht geändert werden.</div>
      </div>
      <div class="mb-3">
        <label for="username" class="form-label">Benutzername</label>
        <input type="text" id="username" name="username" class="form-control" value="<?= esc($user->username) ?>">
      </div>
      <button type="submit" class="btn btn-primary">Details speichern</button>
      <?= form_close() ?>

      <hr class="my-4">

      <h4>Passwort ändern</h4>
      <p>Um Ihr Passwort zu ändern, geben Sie bitte Ihr altes und Ihr neues Passwort ein.</p>
      <?= form_open(route_to('shield_action_password')) ?>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label for="old_password" class="form-label">Altes Passwort</label>
        <input type="password" id="old_password" name="old_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Neues Passwort</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password_confirm" class="form-label">Neues Passwort bestätigen</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Passwort ändern</button>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>