<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<img src="<?= site_url('assets/img/logo.png') ?>" class="rounded mx-auto d-block mb-5 mt-5" style="width:150px;" alt="">
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.emailEnterCode') ?></h5>

        <p><?= lang('Auth.emailConfirmCode') ?></p>

        <?php if (session('error') !== null) : ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-verify') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Code -->
            <div class="mb-2">
                <input type="number" class="form-control" name="token" placeholder="000000"
                    inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required>
            </div>

            <div class="d-grid col-8 mx-auto m-3">
                <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.confirm') ?></button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>