<?= $this->extend('shield/layout') ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<img src="<?= site_url('assets/img/logo.png') ?>" class="rounded mx-auto d-block mb-5 mt-5" style="width:150px;" alt="">
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-5"><?= lang('Auth.useMagicLink') ?></h5>

        <p><b><?= lang('Auth.checkYourEmail') ?></b></p>

        <p><?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?></p>
    </div>
</div>


<?= $this->endSection() ?>