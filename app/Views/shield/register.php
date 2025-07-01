<?= $this->extend('shield/layout') ?>
<?= $this->section('title') ?><?= lang('Auth.register') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container">
    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/wursti-logo-frontpage.png" class="auth-logo" alt="Wurstify Logo">
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card auth-card">
                <div class="card-body p-4 p-sm-5 pb-0">
                    <h5 class="card-title text-center mb-4"><?= lang('Auth.register') ?></h5>

                    <form action="<?= url_to('register') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" name="email" value="<?= old('email') ?>" required>
                            <label>E-Mail</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="username" value="<?= old('username') ?>" required>
                            <label>Benutzername</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password" required>
                            <label>Passwort</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password_confirm" required>
                            <label>Passwort bestätigen</label>
                        </div>

                        <div class="d-grid col-12 mx-auto mt-4">
                            <button type="submit" class="btn btn-primary btn-lg"><?= lang('Auth.register') ?></button>
                        </div>
                    </form>
                </div>
                <div class="auth-switch-link">
                    <p><?= lang('Auth.haveAccount') ?> <a href="<?= url_to('login') ?>"><?= lang('Auth.login') ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>