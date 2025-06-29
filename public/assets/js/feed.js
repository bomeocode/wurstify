document.addEventListener("DOMContentLoaded", function () {
  const feedList = document.getElementById("feed-list");
  const loadingIndicator = document.getElementById("loading-indicator");
  const trigger = document.getElementById("load-more-trigger");

  // Wir holen uns die Modal-Elemente aus dem Haupt-DOM
  const modalElement = document.getElementById("ajax-modal");
  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  let nextPage = 1;
  let isLoading = false;
  let lightbox;

  function renderStars(score) {
    // Wenn keine Punktzahl vorhanden oder 0 ist, einen Platzhalter-Text zurückgeben
    if (!score || score <= 0) {
      return '<small class="text-muted">Nicht bewertet</small>';
    }

    // Wir runden auf die nächste ganze Zahl, um z.B. aus 4.5 eine 5 zu machen
    const solidScore = Math.round(score);

    // Erzeugt die gefüllten und leeren Sterne
    const solidStars = "★".repeat(solidScore);
    const emptyStars = "☆".repeat(5 - solidScore);

    // Gibt den vollständigen String zurück
    return solidStars + emptyStars;
  }

  async function loadFeedItems() {
    if (isLoading || !nextPage) return;
    isLoading = true;
    loadingIndicator.innerHTML =
      '<div class="spinner-border" role="status"></div>';
    loadingIndicator.style.display = "block";

    try {
      const response = await fetch(`/api/feed/ratings?page=${nextPage}`);
      const data = await response.json();

      if (data.ratings && data.ratings.length > 0) {
        data.ratings.forEach((rating) => {
          const card = document.createElement("div");
          card.className = "card shadow-sm mb-4";

          let comment = rating.comment || "";
          let needsReadMore = comment.length > 150;
          let shortComment = needsReadMore
            ? comment.substring(0, 150).replace(/\s+\S*$/, "") + "..."
            : comment;

          // NEU: Wir berechnen den Gesamtdurchschnitt direkt hier
          const avg =
            (parseFloat(rating.rating_taste) +
              parseFloat(rating.rating_appearance) +
              parseFloat(rating.rating_presentation) +
              parseFloat(rating.rating_price) +
              parseFloat(rating.rating_service)) /
            5;

          // HIER IST DIE ERWEITERTE HTML-STRUKTUR
          card.innerHTML = `
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                           <h5 class="mb-0">${rating.vendor_name}</h5>
                           <small class="text-muted">${
                             rating.vendor_address
                           }</small>
                        </div>
                        <div class="text-center">
                            <h2 class="display-6 fw-bold mb-0">${avg.toFixed(
                              1
                            )}</h2>
                            <div class="text-warning">${renderStars(avg)}</div>
                        </div>
                    </div>
                    <div class="card-body">
                        ${
                          comment
                            ? `
                            <p class="card-text fst-italic">"${shortComment}"</p>
                            ${
                              needsReadMore
                                ? `<button class="btn btn-link p-0 open-single-rating-modal" data-url="/api/ratings/${rating.id}">Weiterlesen</button><hr class="my-3">`
                                : '<hr class="my-3">'
                            }
                        `
                            : ""
                        }
                        
                        <div>
                            <div class="d-flex justify-content-between"><small>Aussehen:</small> <span class="text-warning">${renderStars(
                              rating.rating_appearance
                            )}</span></div>
                            <div class="d-flex justify-content-between"><small>Geschmack:</small> <span class="text-warning">${renderStars(
                              rating.rating_taste
                            )}</span></div>
                            <div class="d-flex justify-content-between"><small>Präsentation:</small> <span class="text-warning">${renderStars(
                              rating.rating_presentation
                            )}</span></div>
                            <div class="d-flex justify-content-between"><small>Preis/Leistung:</small> <span class="text-warning">${renderStars(
                              rating.rating_price
                            )}</span></div>
                            <div class="d-flex justify-content-between"><small>Personal/Service:</small> <span class="text-warning">${renderStars(
                              rating.rating_service
                            )}</span></div>
                        </div>

                        ${
                          rating.image1 || rating.image2 || rating.image3
                            ? `
                            <div class="rating-images mt-3">
                                <div class="row g-2">
                                    ${
                                      rating.image1
                                        ? `<div class="col-3"><a href="/uploads/ratings/${rating.image1}" class="glightbox" data-gallery="feed-${rating.id}"><img src="/uploads/ratings/${rating.image1}" class="img-fluid rounded" alt="Bild 1"></a></div>`
                                        : ""
                                    }
                                    ${
                                      rating.image2
                                        ? `<div class="col-3"><a href="/uploads/ratings/${rating.image2}" class="glightbox" data-gallery="feed-${rating.id}"><img src="/uploads/ratings/${rating.image2}" class="img-fluid rounded" alt="Bild 2"></a></div>`
                                        : ""
                                    }
                                    ${
                                      rating.image3
                                        ? `<div class="col-3"><a href="/uploads/ratings/${rating.image3}" class="glightbox" data-gallery="feed-${rating.id}"><img src="/uploads/ratings/${rating.image3}" class="img-fluid rounded" alt="Bild 3"></a></div>`
                                        : ""
                                    }
                                </div>
                            </div>
                        `
                            : ""
                        }
                    </div>
                    <div class="card-footer text-muted text-end">
                       Bewertet von <strong>${
                         rating.username || "Anonym"
                       }</strong> am ${new Date(
            rating.created_at
          ).toLocaleDateString("de-DE")}
                    </div>
                `;
          feedList.appendChild(card);
        });

        // GLightbox neu initialisieren, falls es schon existiert
        if (typeof GLightbox === "function") {
          if (lightbox) lightbox.reload();
          else {
            lightbox = GLightbox({ selector: ".glightbox" });
          }
        }

        nextPage =
          data.pager.currentPage < data.pager.pageCount
            ? data.pager.currentPage + 1
            : null;
      } else {
        nextPage = null;
      }
    } catch (error) {
      console.error("Fehler beim Laden des Feeds:", error);
    } finally {
      isLoading = false;
      if (!nextPage) {
        // Wir ersetzen den Spinner komplett durch eine Textnachricht
        loadingIndicator.innerHTML =
          '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        // Und stellen sicher, dass der Container sichtbar ist
        loadingIndicator.style.display = "block";
      } else {
        // Wenn es noch Seiten gibt, verstecken wir den Indikator wieder
        loadingIndicator.style.display = "none";
      }
    }
  }

  // Event-Listener für "Weiterlesen"-Buttons
  document.addEventListener("click", async function (e) {
    if (e.target.classList.contains("open-single-rating-modal")) {
      const url = e.target.dataset.url;
      modalTitle.textContent = "Lade Bewertung...";
      modalBody.innerHTML =
        '<div class="text-center p-5"><div class="spinner-border"></div></div>';
      bsModal.show();
      try {
        const response = await fetch(url, {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        if (!response.ok)
          throw new Error("Inhalt konnte nicht geladen werden.");

        const html = await response.text();
        modalBody.innerHTML = html;
        modalTitle.textContent =
          modalBody.querySelector("h1")?.textContent || "Bewertung";

        // +++ HIER IST DIE LÖSUNG +++
        // Wenn eine Lightbox-Instanz existiert, sage ihr, dass sie
        // nach neuen Links auf der Seite (also auch im Modal) suchen soll.
        if (lightbox) {
          lightbox.reload();
        }
      } catch (error) {
        modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
      }
    }
  });

  // Intersection Observer für Lazy Loading
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && !isLoading) {
        // Diese Zeile nur, wenn der Trigger wirklich sichtbar wird

        loadFeedItems();
      }
    },
    {
      root: null, // Beobachtet den Haupt-Browser-Viewport
      threshold: 0.1, // Löst aus, wenn 10% des Elements sichtbar sind
    }
  );

  if (trigger) {
    observer.observe(trigger);
  }

  // Lade die ersten Einträge
  loadFeedItems();
});
