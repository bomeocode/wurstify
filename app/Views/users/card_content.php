<?php
// app/Views/users/card_content.php
$level = $user->getLevel();
?>
<div class="text-center">
  <img src="<?= $user->avatar ? '/uploads/avatars/' . esc($user->avatar, 'attr') : '/assets/img/avatar-placeholder.png' ?>"
    alt="Avatar von <?= esc($user->username) ?>"
    class="rounded-circle mb-3"
    style="width: 120px; height: 120px; object-fit: cover;">

  <h2 class="h4"><?= esc($user->username) ?></h2>

  <?php if ($level): ?>
    <p><span class="badge bg-primary"><?= esc($level->name) ?></span></p>
  <?php endif; ?>

  <?php if (!empty($user->bio)): ?>
    <p class="text-muted fst-italic">"<?= esc($user->bio) ?>"</p>
  <?php endif; ?>
</div>