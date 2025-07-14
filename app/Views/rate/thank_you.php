<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Vielen Dank!<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="container my-5 text-center">
  <h1 class="display-4">👍</h1>
  <h1 class="h2">Vielen Dank für deine Bewertung!</h1>
  <p class="lead">Dein Feedback hilft anderen Wurst-Fans, die beste Wurst zu finden.</p>
  <a href="/" class="btn btn-primary mt-3">Zurück zur Startseite</a>
</div>
<?= $this->endSection() ?>