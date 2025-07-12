<?= $this->extend('layouts/main_light') ?>
<?= $this->section('title') ?>Wurst-Feed<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-5" x-data="feedComponent()">
  <h1 class="mb-4">Der Wurst-Feed</h1>
  <p class="text-muted">Die neuesten Bewertungen aus der ganzen Republik.</p>

  <div id="feed-list">
  </div>

  <div id="loading-indicator" class="text-center my-4" x-show="isLoading">
    <div class="spinner-border" role="status"></div>
  </div>

  <div x-show="!isLoading && nextPage" x-intersect:enter="loadMoreItems()"></div>

  <template x-if="!nextPage && itemsLoaded">
    <p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>
  </template>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function feedComponent() {
    return {
      isLoading: false,
      nextPage: 1,
      itemsLoaded: false, // Um die "Ende"-Nachricht erst nach dem ersten Laden anzuzeigen

      loadMoreItems() {
        if (this.isLoading || !this.nextPage) return;

        this.isLoading = true;

        fetch(`/api/feed/ratings?page=${this.nextPage}`)
          .then(response => response.json())
          .then(data => {
            const feedList = document.getElementById('feed-list');
            feedList.insertAdjacentHTML('beforeend', data.html);

            if (window.initOrReloadLightbox) window.initOrReloadLightbox();

            this.nextPage = (data.pager.currentPage < data.pager.pageCount) ?
              data.pager.currentPage + 1 :
              null;

            this.itemsLoaded = true;
            this.isLoading = false;

            // Lightbox für neu geladene Elemente neu initialisieren
            if (window.GLightbox) {
              window.GLightbox({
                selector: '.glightbox'
              }).reload();
            }
          })
          .catch(() => {
            this.isLoading = false;
            // Hier könnte ein Fehler-Toast angezeigt werden
          });
      },

      init() {
        // Lade die erste Seite sofort
        this.loadMoreItems();
      }
    }
  }
</script>
<?= $this->endSection() ?>