<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>EduScreen - Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h1>Willkommen im Dashboard, <?= esc($user->username) ?>!</h1>
<a href="/logout">Abmelden</a>
<hr>

<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Impedit pariatur voluptas consequatur quasi id ipsa at est iste tempora consequuntur necessitatibus cumque doloremque perferendis, facilis quo vero in voluptatum. Nesciunt?
</p>

<button class="btn btn-primary btn-lg">Jetzt eine Bratwurst bestellen</button>

<div class="alert alert-success mt-3" role="alert">
  Ihre Bestellung war erfolgreich! Guten Appetit!
</div>

<p class="text-secondary mt-3">
  Ein klassischer Begleiter zum Grillfest.
</p>

<?= $this->endSection() ?>