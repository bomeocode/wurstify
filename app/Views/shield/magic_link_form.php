<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="col-12 text-center">
    <img src="/assets/img/wursti-logo-frontpage.png" class="auth-logo" alt="Wurstify Logo">
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.useMagicLink') ?></h5>

        <?php if (session('error') !== null) : ?>
            <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
        <?php elseif (session('errors') !== null) : ?>
            <div class="alert alert-danger" role="alert">
                <?php if (is_array(session('errors'))) : ?>
                    <?php foreach (session('errors') as $error) : ?>
                        <?= $error ?>
                        <br>
                    <?php endforeach ?>
                <?php else : ?>
                    <?= session('errors') ?>
                <?php endif ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('magic-link') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Email -->
            <div class="form-floating mb-2">
                <input type="email" class="form-control" id="floatingEmailInput" name="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>"
                    value="<?= old('email', auth()->user()->email ?? null) ?>" required>
                <label for="floatingEmailInput"><?= lang('Auth.email') ?></label>
            </div>

            <div class="d-grid col-12 col-md-8 mx-auto m-3">
                <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.send') ?></button>
            </div>

        </form>

        <p class="text-center"><a href="<?= url_to('login') ?>"><?= lang('Auth.backToLogin') ?></a></p>
    </div>
</div>

<?= $this->endSection() ?>