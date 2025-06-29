<?php
// Helfer-Funktion für die Sterne, direkt in der View
function render_stars_helper($score)
{
  if (!$score || $score <= 0) return '<small class="text-muted">Nicht bewertet</small>';
  $s = round($score);
  return str_repeat('★', $s) . str_repeat('☆', 5 - $s);
}

// Gesamt-Durchschnitt für diese eine Bewertung berechnen
$avg = ($rating['rating_taste'] + $rating['rating_appearance'] + $rating['rating_presentation'] + $rating['rating_price'] + $rating['rating_service']) / 5;
?>

<div class="container-fluid">
  <h1><?= esc($rating['vendor_name']) ?></h1>
  <p class="text-muted"><?= esc($rating['vendor_address']) ?></p>

  <hr>

  <div class="card border-0">
    <div class="card-body p-0">
      <div class="row g-3">
        <div class="col-md-3 text-center d-flex flex-column justify-content-center align-items-center p-2 bg-light rounded">
          <h2 class="display-5 fw-bold mb-0"><?= number_format($avg, 1) ?></h2>
          <div class="text-warning"><?= render_stars_helper($avg) ?></div>
          <small class="text-muted">Gesamt</small>
        </div>
        <div class="col-md-9">
          <h6 class="card-title mb-1">
            <strong><?= esc($rating['username'] ?? 'Anonym') ?></strong>
            <small class="text-muted"> schrieb am <?= (new DateTime($rating['created_at']))->format('d.m.Y H:i') ?>:</small>
          </h6>
          <p class="card-text fst-italic">"<?= $rating['comment'] ? esc($rating['comment']) : 'Kein Kommentar' ?>"</p>
          <hr class="my-2">
          <div>
            <div class="d-flex justify-content-between"><small>Aussehen:</small> <span class="text-warning"><?= render_stars_helper($rating['rating_appearance']) ?></span></div>
            <div class="d-flex justify-content-between"><small>Geschmack:</small> <span class="text-warning"><?= render_stars_helper($rating['rating_taste']) ?></span></div>
            <div class="d-flex justify-content-between"><small>Präsentation:</small> <span class="text-warning"><?= render_stars_helper($rating['rating_presentation']) ?></span></div>
            <div class="d-flex justify-content-between"><small>Preis/Leistung:</small> <span class="text-warning"><?= render_stars_helper($rating['rating_price']) ?></span></div>
            <div class="d-flex justify-content-between"><small>Personal/Service:</small> <span class="text-warning"><?= render_stars_helper($rating['rating_service']) ?></span></div>
          </div>
          <?php if (!empty($rating['image1']) || !empty($rating['image2']) || !empty($rating['image3'])): ?>
            <div class="rating-images mt-3">
              <div class="row g-2">
                <?php if (!empty($rating['image1'])): ?><div class="col-4"><a href="/uploads/ratings/<?= esc($rating['image1'], 'attr') ?>" class="glightbox" data-gallery="modal-rating-<?= $rating['id'] ?>"><img src="/uploads/ratings/<?= esc($rating['image1'], 'attr') ?>" class="img-fluid rounded" alt="Bild 1"></a></div><?php endif; ?>
                <?php if (!empty($rating['image2'])): ?><div class="col-4"><a href="/uploads/ratings/<?= esc($rating['image2'], 'attr') ?>" class="glightbox" data-gallery="modal-rating-<?= $rating['id'] ?>"><img src="/uploads/ratings/<?= esc($rating['image2'], 'attr') ?>" class="img-fluid rounded" alt="Bild 2"></a></div><?php endif; ?>
                <?php if (!empty($rating['image3'])): ?><div class="col-4"><a href="/uploads/ratings/<?= esc($rating['image3'], 'attr') ?>" class="glightbox" data-gallery="modal-rating-<?= $rating['id'] ?>"><img src="/uploads/ratings/<?= esc($rating['image3'], 'attr') ?>" class="img-fluid rounded" alt="Bild 3"></a></div><?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>