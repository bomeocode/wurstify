<?php
$openingHours = json_decode($vendor['opening_hours'] ?? '[]', true);
$socialMedia = json_decode($vendor['social_media'] ?? '[]', true);
?>

<div class="vendor-header" style="background-color: #6c757d; <?= $vendor['cover_image'] ? "background-image: url('/uploads/vendors/" . esc($vendor['cover_image'], 'attr') . "');" : '' ?>">
  <div class="vendor-header-overlay">
    <img src="<?= $vendor['logo_image'] ? '/uploads/vendors/' . esc($vendor['logo_image'], 'attr') : '/assets/img/avatar-placeholder.png' ?>" class="vendor-logo shadow" alt="Logo">
  </div>
</div>

<div class="container-fluid px-3 py-4">
  <div class="text-center" style="margin-top: 60px; margin-bottom: 1rem;">
    <h1 class="h3"><?= esc($vendor['name']) ?></h1>
    <?php if ($vendor['category'] === 'mobil'): ?>
      <span class="badge bg-warning text-dark">Mobil / Event</span>
    <?php endif; ?>
    <p class="text-muted mb-2"><?= esc($vendor['address']) ?></p>
    <p><b><?= number_format((float)$vendor['avg_taste'], 1) ?> ★</b> <span class="text-muted">(aus <?= $vendor['total_ratings'] ?> Bewertungen)</span></p>
  </div>

  <hr class="my-4">
  <h3>Alle Bewertungen</h3>
  <div class="text-center text-muted p-4 border rounded">
    <p class="mb-0">Die Liste Ihrer Bewertungen wird auf der öffentlichen Anbieter-Seite angezeigt.</p>
  </div>
</div>