<?php
$rating = (object) $rating;
$ratingData = json_encode([
  'id' => $rating->id,
  'comment' => $rating->comment ?? '',
  'helpful_count' => $rating->helpful_count ?? 0,
  'user_has_voted' => $rating->user_has_voted ?? false,
  'user' => ['id' => $rating->user_id, 'name' => $rating->username ?? 'Anonym', 'avatar' => $rating->avatar ? '/uploads/avatars/' . $rating->avatar : '/assets/img/avatar-placeholder.png'],
  'vendor' => ['uuid' => $rating->vendor_uuid ?? null, 'name' => $rating->vendor_name ?? null, 'category' => $rating->vendor_category ?? null, 'address' => $rating->vendor_address ?? null],
  'avg' => number_format(($rating->rating_taste + $rating->rating_appearance + $rating->rating_presentation + $rating->rating_price + $rating->rating_service) / 5, 1),
  'details' => ['Aussehen' => round($rating->rating_appearance), 'Geschmack' => round($rating->rating_taste), 'Präsentation' => round($rating->rating_presentation), 'Preis/Leistung' => round($rating->rating_price), 'Personal/Service' => round($rating->rating_service)],
  'images' => array_values(array_filter([$rating->image1, $rating->image2, $rating->image3]))
]);
?>

<div class="card shadow-sm mb-4" x-data="ratingCard(<?= htmlspecialchars($ratingData) ?>)">

  <?php if ($context === 'feed'): ?>
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
      <div>
        <h5 class="mb-0">
          <a href="#" class="text-dark text-decoration-none open-offcanvas" :data-url="`/api/vendors/details/${vendor.uuid}`" :title="`Details für ${vendor.name}`">
            <span x-text="vendor.name"></span>
          </a>
          <template x-if="vendor.category === 'mobil'">
            <span class="badge bg-warning text-dark ms-2">Mobil</span>
          </template>
        </h5>
        <small class="text-muted" x-text="vendor.address"></small>
      </div>
      <div class="text-center ps-3">
        <h2 class="display-6 fw-bold mb-0" x-text="avg"></h2>
        <div class="text-warning" style="font-size: 0.8rem;" x-html="renderStars(avg)"></div>
      </div>
    </div>
  <?php endif; ?>

  <div class="card-body">
    <div class="d-flex align-items-center mb-3">
      <a href="#" class="open-user-modal" :data-url="`/api/users/${user.id}`" :title="`Benutzerprofil von ${user.name}`">
        <img :src="user.avatar" class="avatar-image-sm rounded-circle me-2">
      </a>
      <div>
        <small class="text-muted">Bewertung von</small>
        <a href="#" class="open-modal text-dark text-decoration-none" :data-url="`/api/users/${user.id}`" :title="`Benutzerprofil von ${user.name}`">
          <strong x-text="user.name"></strong>
        </a>
      </div>
    </div>

    <div x-data="{ expanded: false }">
      <p class="card-text fst-italic" x-show="!expanded" x-transition x-html="shortComment"></p>
      <p class="card-text fst-italic" x-show="expanded" x-transition.collapse x-html="fullComment"></p>
      <template x-if="needsReadMore">
        <button @click="expanded = !expanded" class="btn btn-sm btn-link p-0">
          <span x-show="!expanded">Mehr anzeigen <i class="bi bi-caret-down-fill"></i></span>
          <span x-show="expanded">Weniger anzeigen <i class="bi bi-caret-up-fill"></i></span>
        </button>
      </template>
    </div>

    <hr class="my-3">
    <div class="row">
      <template x-for="(score, name) in details">
        <div class="col-md-6">
          <div class="d-flex justify-content-between"><small x-text="name + ':'"></small><span class="text-warning small" x-html="renderStars(score)"></span></div>
        </div>
      </template>
    </div>

    <template x-if="images.length > 0">
      <div class="rating-images mt-3">
        <div class="row g-2">
          <template x-for="(image, index) in images">
            <div class="col-4">
              <a :href="`/uploads/ratings/${image}`" class="glightbox" :data-gallery="`rating-${id}`">
                <img :src="`/uploads/ratings/${image}`" class="img-fluid rounded" :alt="`Bild ${index + 1}`">
              </a>
            </div>
          </template>
        </div>
      </div>
    </template>
  </div>

  <div class="card-footer text-muted d-flex justify-content-between align-items-center small py-2">
    <div>
      <button type="button" class="btn btn-sm" :class="user_has_voted ? 'btn-success' : 'btn-outline-success'" @click="toggleVote()" :disabled="loading">
        <i class="bi bi-hand-thumbs-up"></i> Hilfreich
        <span class="badge ms-1" :class="user_has_voted ? 'bg-light text-success' : 'bg-success'" x-text="helpful_count"></span>
      </button>
    </div>
    <small>Bewertet am <?= date('d.m.Y', strtotime($rating->created_at)) ?></small>
  </div>
</div>