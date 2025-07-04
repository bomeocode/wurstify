<?= $this->extend('shield/layout') ?>
<?= $this->section('title') ?><?= lang('Auth.login') ?> <?= $this->endSection() ?>

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
                    <h5 class="card-title text-center mb-4"><?= lang('Auth.login') ?></h5>

                    <?php if (session('error') !== null) : ?>
                        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
                    <?php endif ?>
                    <form action="<?= url_to('login') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingCredentialInput" name="credential" placeholder="Benutzername oder E-Mail" required>
                            <label for="floatingCredentialInput">Benutzername oder E-Mail</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="floatingPasswordInput" name="password" required>
                            <label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
                        </div>

                        <div class="d-grid col-12 mx-auto mt-4">
                            <button type="submit" class="btn btn-primary btn-lg"><?= lang('Auth.login') ?></button>
                        </div>
                    </form>
                </div>

                <div class="auth-switch-link">
                    <?php if (setting('Auth.allowRegistration')) : ?>
                        <p class="mb-0"><?= lang('Auth.needAccount') ?> <a href="<?= url_to('register') ?>"><?= lang('Auth.register') ?></a></p>
                    <?php endif ?>

                    <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                        <p class="text-center mt-2">
                            <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.forgotPassword') ?></a>
                        </p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
</div>

<?= $this->endSection() ?>