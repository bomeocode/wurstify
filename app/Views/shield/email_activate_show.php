<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<img src="<?= site_url('assets/img/logo.png') ?>" class="rounded mx-auto d-block mb-5 mt-5" style="width:150px;" alt="">
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.emailActivateTitle') ?></h5>

        <?php if (session('error')) : ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif ?>

        <p><?= lang('Auth.emailActivateBody') ?></p>

        <form action="<?= url_to('auth-action-verify') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Code -->
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="floatingTokenInput" name="token" placeholder="000000" inputmode="numeric"
                    pattern="[0-9]*" autocomplete="one-time-code" value="<?= old('token') ?>" required>
                <label for="floatingTokenInput"><?= lang('Auth.token') ?></label>
            </div>

            <div class="d-grid col-8 mx-auto m-3">
                <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.send') ?></button>
            </div>

        </form>
    </div>
</div>


<?= $this->endSection() ?>