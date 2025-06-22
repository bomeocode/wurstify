document.addEventListener("DOMContentLoaded", function () {
  const ratingsList = document.getElementById("ratings-list");
  const loadingIndicator = document.getElementById("loading-indicator");
  const trigger = document.getElementById("load-more-trigger");
  const vendorUuid = window.location.pathname.split("/").pop();

  let nextPage = 1;
  let isLoading = false;

  async function loadRatings() {
    if (isLoading || !nextPage) return;

    isLoading = true;
    loadingIndicator.style.display = "block";

    // --- NEU: Eine kleine Helfer-Funktion zum Zeichnen der Sterne ---
    function renderStars(score) {
      if (!score || score <= 0) return "Nicht bewertet";
      const solidStars = "★".repeat(score);
      const emptyStars = "☆".repeat(5 - score);
      return solidStars + emptyStars;
    }

    try {
      const response = await fetch(
        `/api/vendors/${vendorUuid}/ratings?page=${nextPage}`
      );
      const data = await response.json();

      if (data.ratings && data.ratings.length > 0) {
        data.ratings.forEach((rating) => {
          const ratingEl = document.createElement("div");
          ratingEl.className = "card mb-3";

          // --- NEU: Durchschnitt für diese eine Bewertung berechnen ---
          const overallAvg =
            (parseFloat(rating.rating_taste) +
              parseFloat(rating.rating_appearance) +
              parseFloat(rating.rating_presentation) +
              parseFloat(rating.rating_price) +
              parseFloat(rating.rating_service)) /
            5;

          // --- NEU: Verbessertes HTML-Template für die Bewertungskarte ---
          ratingEl.innerHTML = `
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center d-flex flex-column justify-content-center align-items-center">
                                <h2 class="display-4 fw-bold">${overallAvg.toFixed(
                                  1
                                )}</h2>
                                <div class="text-warning">${renderStars(
                                  Math.round(overallAvg)
                                )}</div>
                                <small class="text-muted">Gesamt</small>
                            </div>

                            <div class="col-md-9">
                                <h5 class="card-title">${
                                  rating.username || "Anonym"
                                } schrieb am ${new Date(
            rating.created_at.date || rating.created_at
          ).toLocaleDateString("de-DE")}:</h5>
                                <p class="card-text">${
                                  rating.comment || "<i>Kein Kommentar</i>"
                                }</p>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><small>Aussehen:</small> <span class="text-warning">${renderStars(
                                          rating.rating_appearance
                                        )}</span></p>
                                        <p class="mb-1"><small>Geschmack:</small> <span class="text-warning">${renderStars(
                                          rating.rating_taste
                                        )}</span></p>
                                        <p class="mb-1"><small>Präsentation:</small> <span class="text-warning">${renderStars(
                                          rating.rating_presentation
                                        )}</span></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><small>Preis/Leistung:</small> <span class="text-warning">${renderStars(
                                          rating.rating_price
                                        )}</span></p>
                                        <p class="mb-1"><small>Personal/Service:</small> <span class="text-warning">${renderStars(
                                          rating.rating_service
                                        )}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
          ratingsList.appendChild(ratingEl);
        });

        if (data.ratings.length < 10) {
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
      isLoading = false;
    } catch (error) {
      console.error("Fehler beim Laden der Bewertungen:", error);
      loadingIndicator.innerHTML =
        '<p class="text-danger text-center my-4">Laden der Bewertungen fehlgeschlagen.</p>';
    }
  }

  // Intersection Observer zum Auslösen des Ladevorgangs
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        loadRatings();
      }
    },
    { threshold: 1.0 }
  );

  observer.observe(trigger);

  // Lade die erste Seite sofort
  loadRatings();
});
