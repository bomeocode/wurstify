<?php
// --- Helfer-Variablen für die View vorbereiten ---
$openingHours = json_decode($vendor['opening_hours'] ?? '[]', true);
$socialMedia = json_decode($vendor['social_media'] ?? '[]', true);
$hasOwner = !empty($vendor['owner_user_id']);

$avgRating = number_format((
  floatval($vendor['avg_taste']) +
  floatval($vendor['avg_appearance']) +
  floatval($vendor['avg_presentation']) +
  floatval($vendor['avg_price']) +
  floatval($vendor['avg_service'])
) / 5, 1);
?>

<div class="vendor-header" style="background-color: #6c757d; <?= $vendor['cover_image'] ? "background-image: url('/uploads/vendors/" . esc($vendor['cover_image'], 'attr') . "');" : '/assets/img/vendor-cover-placeholder.jpg' ?>">
  <div class="vendor-header-overlay">
    <img src="<?= $vendor['logo_image'] ? '/uploads/vendors/' . esc($vendor['logo_image'], 'attr') : '/assets/img/avatar-placeholder.png' ?>" class="vendor-logo shadow" alt="Logo">
  </div>
</div>

<div class="container-fluid px-3 py-4">

  <div class="text-center" style="margin-top: 40px; margin-bottom: 1rem;">
    <h1 class="h3"><?= esc($vendor['vendor_name']) ?></h1>
    <?php if ($vendor['category'] === 'mobil'): ?>
      <span class="badge bg-warning text-dark">Mobil / Event</span>
    <?php endif; ?>
    <p class="text-muted mb-2"><?= esc($vendor['address']) ?></p>
    <p><b><?= $avgRating ?> ★</b> <span class="text-muted">(aus <?= $vendor['total_ratings'] ?> Bewertungen)</span></p>
  </div>

  <div class="d-grid my-4">
    <button type="button" class="btn btn-lg btn-warning open-modal-form" data-url="/ratings/new?vendor_uuid=<?= esc($vendor['uuid']) ?>">
      ★ Diesen Anbieter jetzt bewerten
    </button>
  </div>

  <?php if ($hasOwner): ?>

    <?php if (!empty($vendor['description'])): ?>
      <div class="card my-4">
        <div class="card-body">
          <h5 class="card-title">Über uns</h5>
          <p class="card-text"><?= nl2br(esc($vendor['description'])) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">Öffnungszeiten</h5>
            <ul class="list-unstyled opening-hours-list">
              <?php
              $days = ['monday' => 'Mo', 'tuesday' => 'Di', 'wednesday' => 'Mi', 'thursday' => 'Do', 'friday' => 'Fr', 'saturday' => 'Sa', 'sunday' => 'So'];
              foreach ($days as $key => $day): ?>
                <li>
                  <span><?= $day ?></span>
                  <span class="fw-bold"><?= esc($openingHours[$key] ?? 'geschlossen') ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title mb-3">Kontakt & Links</h5>
            <div class="d-grid gap-2">
              <?php if (!empty($vendor['website_url'])): ?>
                <a href="<?= esc($vendor['website_url'], 'attr') ?>" target="_blank" rel="noopener nofollow" class="btn btn-outline-primary"><i class="bi bi-globe me-2"></i>Webseite</a>
              <?php endif; ?>
              <?php if (!empty($socialMedia['facebook'])): ?>
                <a href="https://facebook.com/<?= esc($socialMedia['facebook'], 'attr') ?>" target="_blank" rel="noopener nofollow" class="btn btn-outline-primary"><i class="bi bi-facebook me-2"></i>Facebook</a>
              <?php endif; ?>
              <?php if (!empty($socialMedia['instagram'])): ?>
                <a href="https://instagram.com/<?= esc($socialMedia['instagram'], 'attr') ?>" target="_blank" rel="noopener nofollow" class="btn btn-outline-primary"><i class="bi bi-instagram me-2"></i>Instagram</a>
              <?php endif; ?>
              <?php if (!empty($socialMedia['tiktok'])): ?>
                <a href="https://tiktok.com/@<?= esc($socialMedia['tiktok'], 'attr') ?>" target="_blank" rel="noopener nofollow" class="btn btn-outline-primary"><i class="bi bi-tiktok me-2"></i>TikTok</a>
              <?php endif; ?>
              <?php if (!empty($socialMedia['youtube'])): ?>
                <a href="https://youtube.com/<?= esc($socialMedia['youtube'], 'attr') ?>" target="_blank" rel="noopener nofollow" class="btn btn-outline-primary"><i class="bi bi-youtube me-2"></i>YouTube</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <div class="card text-center border-primary my-4">
      <div class="card-body">
        <h5 class="card-title">Ist das Ihr Imbiss?</h5>
        <p class="card-text text-muted">Verwalten Sie Ihren Eintrag, antworten Sie auf Bewertungen und vieles mehr!</p>
        <button type="button" class="btn btn-primary open-modal-form"
          data-url="<?= site_url('claim/form/' . $vendor['uuid']) ?>"
          title="Inhaberschaft beanspruchen">
          Jetzt Inhaberschaft beanspruchen
        </button>
      </div>
    </div>
  <?php endif; ?>

  <hr class="my-4">
  <h3>Alle Bewertungen</h3>
  <div id="ratings-list"></div>
  <div id="loading-indicator" class="text-center my-4" style="display: none;">
    <div class="spinner-border"></div>
  </div>
  <div id="load-more-trigger"></div>
</div>