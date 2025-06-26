// public/js/vendor_show.js
document.addEventListener("DOMContentLoaded", function () {
  const ratingsList = document.getElementById("ratings-list");
  const loadingIndicator = document.getElementById("loading-indicator");
  const trigger = document.getElementById("load-more-trigger");
  const vendorUuid = window.location.pathname.split("/").pop();

  let nextPage = 1;
  let isLoading = false;
  let lightbox; // NEU: Variable für unsere Lightbox-Instanz

  // Kleine Helfer-Funktion zum Zeichnen der Sterne
  function renderStars(score) {
    if (!score || score <= 0) return "Nicht bewertet";
    const solidStars = "★".repeat(Math.round(score));
    const emptyStars = "☆".repeat(5 - Math.round(score));
    return solidStars + emptyStars;
  }

  async function loadRatings() {
    if (isLoading || !nextPage) return;
    isLoading = true;
    loadingIndicator.style.display = "block";

    try {
      const response = await fetch(
        `/api/vendors/${vendorUuid}/ratings?page=${nextPage}`
      );
      const data = await response.json();

      if (data.ratings && data.ratings.length > 0) {
        data.ratings.forEach((rating) => {
          const ratingEl = document.createElement("div");
          ratingEl.className = "card mb-3";
          const overallAvg =
            (parseFloat(rating.rating_taste) +
              parseFloat(rating.rating_appearance) +
              parseFloat(rating.rating_presentation) +
              parseFloat(rating.rating_price) +
              parseFloat(rating.rating_service)) /
            5;

          ratingEl.innerHTML = `
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center d-flex flex-column justify-content-center align-items-center">
                                    <h2 class="display-4 fw-bold">${overallAvg.toFixed(
                                      1
                                    )}</h2>
                                    <div class="text-warning">${renderStars(
                                      overallAvg
                                    )}</div>
                                    <small class="text-muted">Gesamt</small>
                                </div>
                                <div class="col-md-9">
                                    <h5 class="card-title">${
                                      rating.username || "Anonym"
                                    } schrieb am ${new Date(
            rating.created_at
          ).toLocaleDateString("de-DE")}:</h5>
                                    <p class="card-text fst-italic">"${
                                      rating.comment || "Kein Kommentar"
                                    }"</p>
                                    <hr>
                                    <div class="row"><div class="col-6"><p class="mb-1"><small>Aussehen:</small> <span class="text-warning">${renderStars(
                                      rating.rating_appearance
                                    )}</span></p></div><div class="col-6"><p class="mb-1"><small>Preis/Leistung:</small> <span class="text-warning">${renderStars(
            rating.rating_price
          )}</span></p></div><div class="col-12"><p class="mb-1"><small>Präsentation:</small> <span class="text-warning">${renderStars(
            rating.rating_presentation
          )}</span></p></div><div class="col-6"><p class="mb-1"><small>Geschmack:</small> <span class="text-warning">${renderStars(
            rating.rating_taste
          )}</span></p></div><div class="col-6"><p class="mb-1"><small>Personal/Service:</small> <span class="text-warning">${renderStars(
            rating.rating_service
          )}</span></p></div></div>
                                    ${
                                      rating.image1 ||
                                      rating.image2 ||
                                      rating.image3
                                        ? `<div class="rating-images mt-3"><div class="row g-2">
                                        ${
                                          rating.image1
                                            ? `<div class="col-4"><a href="/uploads/ratings/${rating.image1}" class="glightbox" data-gallery="rating-${rating.id}"><img src="/uploads/ratings/${rating.image1}" class="img-fluid rounded" alt="Bewertungsbild 1"></a></div>`
                                            : ""
                                        }
                                        ${
                                          rating.image2
                                            ? `<div class="col-4"><a href="/uploads/ratings/${rating.image2}" class="glightbox" data-gallery="rating-${rating.id}"><img src="/uploads/ratings/${rating.image2}" class="img-fluid rounded" alt="Bewertungsbild 2"></a></div>`
                                            : ""
                                        }
                                        ${
                                          rating.image3
                                            ? `<div class="col-4"><a href="/uploads/ratings/${rating.image3}" class="glightbox" data-gallery="rating-${rating.id}"><img src="/uploads/ratings/${rating.image3}" class="img-fluid rounded" alt="Bewertungsbild 3"></a></div>`
                                            : ""
                                        }
                                    </div></div>`
                                        : ""
                                    }
                                </div>
                            </div>
                        </div>`;
          ratingsList.appendChild(ratingEl);
        });

        // NEU: Lightbox (neu) initialisieren oder aktualisieren
        if (lightbox) {
          lightbox.reload();
        } else {
          lightbox = GLightbox({
            selector: ".glightbox",
            touchNavigation: true,
            loop: false,
          });
        }

        if (data.pager.pageCount === data.pager.currentPage) {
          nextPage = null;
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        } else {
          nextPage++;
          loadingIndicator.style.display = "none";
        }
      } else {
        nextPage = null;
        if (ratingsList.children.length === 0) {
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Für diesen Anbieter gibt es noch keine Bewertungen.</p>';
        } else {
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        }
      }
    } catch (error) {
      console.error("Fehler beim Laden der Bewertungen:", error);
      loadingIndicator.innerHTML =
        '<p class="text-danger text-center my-4">Laden der Bewertungen fehlgeschlagen.</p>';
    } finally {
      isLoading = false;
    }
  }

  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && !isLoading) {
        loadRatings();
      }
    },
    { threshold: 1.0 }
  );

  if (trigger) {
    observer.observe(trigger);
    loadRatings();
  }
});
