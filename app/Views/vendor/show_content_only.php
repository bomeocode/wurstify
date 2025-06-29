<div class="container">
  <h1><?= esc($vendor['vendor_name']) ?></h1>
  <p>Durchschnittliche Bewertung: <b><?= number_format((floatval($vendor['avg_taste']) + floatval($vendor['avg_appearance']) + floatval($vendor['avg_presentation']) + floatval($vendor['avg_price']) + floatval($vendor['avg_service'])) / 5, 1) ?> ★</b> aus <?= $vendor['total_ratings'] ?> Bewertungen.</p>

  <div class="d-grid my-4">
    <button type="button" class="btn btn-lg btn-warning open-modal-form" data-url="/ratings/new?vendor_uuid=<?= esc($vendor['uuid']) ?>">
      ★ Diesen Anbieter jetzt bewerten
    </button>
  </div>

  <hr>
  <h3>Alle Bewertungen</h3>

  <div id="ratings-list"></div>

  <div id="loading-indicator" class="text-center my-4" style="display: none;">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <div id="load-more-trigger"></div>
</div>