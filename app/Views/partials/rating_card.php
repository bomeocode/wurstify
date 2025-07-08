<?php
// app/Views/partials/rating_card.php

// Wir bereiten die Daten vor, damit wir sie einfacher verwenden können
$rating = (object) $rating; // Stellt sicher, dass wir auf die Daten als Objekt zugreifen können
$avg = ($rating->rating_taste + $rating->rating_appearance + $rating->rating_presentation + $rating->rating_price + $rating->rating_service) / 5;
$comment = $rating->comment ?? '';
$needsReadMore = strlen($comment) > 150;
$shortComment = substr($comment, 0, 150) . '...';
$fullComment = $comment;
?>

<div class="card shadow-sm mb-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h5 class="mb-0">
        <a href="#" class="text-dark text-decoration-none open-vendor-modal" data-url="/vendor/<?= esc($rating->vendor_uuid) ?>" data-vendor-uuid="<?= esc($rating->vendor_uuid) ?>">
          <?= esc($rating->vendor_name) ?>
        </a>
        <?php if ($rating->vendor_category === 'mobil'): ?>
          <span class="badge bg-warning text-dark ms-2">Mobil</span>
        <?php endif; ?>
      </h5>
      <small class="text-muted"><?= esc($rating->vendor_address) ?></small>
    </div>
    <div class="text-center ps-3">
      <h2 class="display-6 fw-bold mb-0"><?= number_format($avg, 1) ?></h2>
      <div class="text-warning" style="font-size: 0.8rem;"><?= str_repeat('★', round($avg)) . str_repeat('☆', 5 - round($avg)) ?></div>
    </div>
  </div>
  <div class="card-body">
    <div class="d-flex align-items-center mb-3">
      <a href="#" class="open-user-modal" data-url="/api/users/<?= esc($rating->user_id) ?>">
        <img src="<?= $rating->avatar ? '/uploads/avatars/' . esc($rating->avatar) : '/assets/img/avatar-placeholder.png' ?>" alt="Avatar" class="avatar-image-sm rounded-circle me-2">
      </a>
      <div>
        <small class="text-muted">Bewertung von</small>
        <a href="#" class="open-user-modal text-dark text-decoration-none" data-url="/api/users/<?= esc($rating->user_id) ?>">
          <strong><?= esc($rating->username) ?></strong>
        </a>
      </div>
    </div>

    <div x-data="{ expanded: false }">
      <p class="card-text fst-italic" x-show="!expanded" x-transition><?= esc($shortComment) ?></p>
      <p class="card-text fst-italic" x-show="expanded" x-transition><?= nl2br(esc($fullComment)) ?></p>

      <template x-if="<?= $needsReadMore ? 'true' : 'false' ?>">
        <button @click="expanded = !expanded" class="btn btn-sm btn-link p-0">
          <span x-show="!expanded">Mehr anzeigen <i class="bi bi-caret-down-fill"></i></span>
          <span x-show="expanded">Weniger anzeigen <i class="bi bi-caret-up-fill"></i></span>
        </button>
      </template>
    </div>

    <hr class="my-3">
    <div class="row">
      <?php
      $categories = [
        'rating_taste' => 'Geschmack',
        'rating_appearance' => 'Aussehen',
        'rating_presentation' => 'Präsentation',
        'rating_price' => 'Preis/Leistung',
        'rating_service' => 'Personal/Service',
      ];
      ?>
      <?php foreach ($categories as $field => $label): ?>
        <div class="col-md-6">
          <div class="d-flex justify-content-between">
            <small><?= $label ?>:</small>
            <span class="text-warning small"><?= str_repeat('★', round($rating->$field)) . str_repeat('☆', 5 - round($rating->$field)) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($rating->image1 || $rating->image2 || $rating->image3): ?>
      <div class="rating-images mt-3">
        <div class="row g-2">
          <?php if ($rating->image1): ?>
            <div class="col-4">
              <a href="/uploads/ratings/<?= esc($rating->image1) ?>" class="glightbox" data-gallery="rating-<?= esc($rating->id) ?>">
                <img src="/uploads/ratings/<?= esc($rating->image1) ?>" class="img-fluid rounded" alt="Bild 1">
              </a>
            </div>
          <?php endif; ?>
          <?php if ($rating->image2): ?>
            <div class="col-4">
              <a href="/uploads/ratings/<?= esc($rating->image2) ?>" class="glightbox" data-gallery="rating-<?= esc($rating->id) ?>">
                <img src="/uploads/ratings/<?= esc($rating->image2) ?>" class="img-fluid rounded" alt="Bild 2">
              </a>
            </div>
          <?php endif; ?>
          <?php if ($rating->image3): ?>
            <div class="col-4">
              <a href="/uploads/ratings/<?= esc($rating->image3) ?>" class="glightbox" data-gallery="rating-<?= esc($rating->id) ?>">
                <img src="/uploads/ratings/<?= esc($rating->image3) ?>" class="img-fluid rounded" alt="Bild 3">
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer text-muted d-flex justify-content-between align-items-center small py-2">

    <div x-data="{ 
      count: <?= $rating->helpful_count ?? 0 ?>, 
      voted: false, 
      loading: false,
      ratingId: <?= $rating->id ?>,

      toggleVote() {
          if ('<?= auth()->logged() ?>') {
              window.location.href = '<?= site_url('login') ?>';
              return;
          }

          this.loading = true;
          const csrfToken = document.querySelector('meta[name=\'X-CSRF-TOKEN-VALUE\']')?.content;

          fetch(`/api/ratings/${this.ratingId}/vote`, {
              method: 'POST',
              headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-TOKEN': csrfToken
              }
          })
          .then(res => {
              if (!res.ok) { throw new Error('Fehler bei der Abstimmung'); }
              return res.json();
          })
          .then(data => {
              this.count = data.new_count;
              this.voted = data.voted;
          })
          .catch(err => console.error(err))
          .finally(() => this.loading = false);
          }
      }">
      <button type="button" class="btn btn-sm"
        :class="voted ? 'btn-success' : 'btn-outline-success'"
        @click="toggleVote()"
        :disabled="loading">

        <i class="bi bi-hand-thumbs-up"></i> Hilfreich

        <span class="badge ms-1" :class="voted ? 'bg-light text-success' : 'bg-success'" x-text="count"></span>
      </button>
    </div>

    <small>Bewertet am <?= date('d.m.Y', strtotime($rating->created_at)) ?></small>
  </div>
</div>