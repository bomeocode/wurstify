<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="col-12 text-center">
    <img src="/assets/img/wursti-logo-frontpage.png" class="auth-logo" alt="Wurstify Logo">
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.useMagicLink') ?></h5>

        <p><b><?= lang('Auth.checkYourEmail') ?></b></p>

        <p><?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?></p>
    </div>
</div>


<?= $this->endSection() ?>