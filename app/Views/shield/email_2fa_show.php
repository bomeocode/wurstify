<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<img src="<?= site_url('assets/img/logo.png') ?>" class="rounded mx-auto d-block mb-5 mt-5" style="width:150px;" alt="">
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.email2FATitle') ?></h5>

        <p><?= lang('Auth.confirmEmailAddress') ?></p>

        <?php if (session('error')) : ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-handle') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Email -->
            <div class="mb-2">
                <input type="email" class="form-control" name="email"
                    inputmode="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>"
                    <?php /** @var CodeIgniter\Shield\Entities\User $user */ ?>
                    value="<?= old('email', $user->email) ?>" required>
            </div>

            <div class="d-grid col-8 mx-auto m-3">
                <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.send') ?></button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>