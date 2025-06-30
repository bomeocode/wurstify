import { showToast } from "./main.js"; // Annahme: Ihre Toast-Funktion wird von hier importiert

document.addEventListener("DOMContentLoaded", function () {
  console.log("Spezialist: feed.js geladen.");

  // === SETUP für diese Seite ===
  const feedList = document.getElementById("feed-list");
  const loadingIndicator = document.getElementById("loading-indicator");
  const trigger = document.getElementById("load-more-trigger");

  // SETUP für das Modal
  const modalElement = document.getElementById("ajax-modal");
  if (!modalElement) return;
  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  // HIER IST DIE KORREKTUR: Fehlende Variablen-Deklarationen
  let nextPage = 1;
  let isLoading = false;
  let lightbox;

  // === EVENT LISTENERS ===
  document.addEventListener("click", async function (e) {
    if (e.target.classList.contains("open-single-rating-modal")) {
      e.preventDefault();
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
        initOrReloadLightbox();
      } catch (error) {
        modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
      }
    }
    // Listener für den globalen Feedback-Button (falls vorhanden)
    const feedbackTrigger = e.target.closest(
      '.open-modal-form[data-url*="feedback"]'
    );
    if (feedbackTrigger) {
      e.preventDefault();
      loadGenericFormtoModal(feedbackTrigger.dataset.url, "Feedback geben");
    }
  });

  // 2. Für das Absenden von Formularen im Modal
  modalElement.addEventListener("submit", async function (e) {
    if (e.target.tagName === "FORM" && e.target.closest("#ajax-modal")) {
      e.preventDefault();
      const form = e.target;
      const submitButton = form.querySelector('button[type="submit"]');
      const originalButtonText = submitButton
        ? submitButton.innerHTML
        : "Speichern";
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML =
          '<span class="spinner-border spinner-border-sm"></span> Sende...';
      }

      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const result = await response.json();
        if (!response.ok) throw result;
        showToast(result.message || "Aktion erfolgreich.", "success");
        bsModal.hide();
      } catch (error) {
        const errorMessage = error.messages
          ? Object.values(error.messages)[0]
          : "Ein Fehler ist aufgetreten.";
        showToast(errorMessage, "danger");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      }
    }
  });

  // === FUNKTIONEN ===
  function renderStars(score) {
    if (!score || score <= 0)
      return '<small class="text-muted">Nicht bewertet</small>';
    const s = Math.round(score);
    return "★".repeat(s) + "☆".repeat(5 - s);
  }

  function initOrReloadLightbox() {
    if (typeof GLightbox !== "function") return;
    if (lightbox) {
      lightbox.reload();
    } else {
      lightbox = GLightbox({ selector: ".glightbox" });
    }
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

          const avg =
            (parseFloat(rating.rating_taste) +
              parseFloat(rating.rating_appearance) +
              parseFloat(rating.rating_presentation) +
              parseFloat(rating.rating_price) +
              parseFloat(rating.rating_service)) /
            5;

          // HIER IST DER VOLLSTÄNDIGE HTML-INHALT FÜR DIE KARTE:
          card.innerHTML = `
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                           <h5 class="mb-0">${rating.vendor_name}</h5>
                           <small class="text-muted">${
                             rating.vendor_address || ""
                           }</small>
                        </div>
                        <div class="text-center ps-3">
                            <h2 class="display-6 fw-bold mb-0">${avg.toFixed(
                              1
                            )}</h2>
                            <div class="text-warning" style="font-size: 0.8rem;">${renderStars(
                              avg
                            )}</div>
                        </div>
                    </div>
                    <div class="card-body">
                        ${
                          comment
                            ? `<p class="card-text fst-italic">"${shortComment}"</p>${
                                needsReadMore
                                  ? `<button class="btn btn-link p-0 mb-3 open-single-rating-modal" data-url="/api/ratings/${rating.id}">Weiterlesen</button>`
                                  : ""
                              }`
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
                    <div class="card-footer text-muted d-flex justify-content-end align-items-center small py-2">
                       <div class="me-2 text-end">
                          Bewertet von <strong>${
                            rating.username || "Anonym"
                          }</strong><br>
                          <span style="font-size: 0.8em;">am ${new Date(
                            rating.created_at
                          ).toLocaleDateString("de-DE")}</span>
                       </div>
                       <img src="${
                         rating.avatar
                           ? "/uploads/avatars/" + rating.avatar
                           : "/assets/img/avatar-placeholder.png"
                       }" 
                            alt="Avatar von ${rating.username || "Anonym"}" 
                            class="avatar-image-sm rounded-circle">
                    </div>
                `;
          feedList.appendChild(card);
        });

        if (typeof GLightbox === "function") {
          if (lightbox) {
            lightbox.reload();
          } else {
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
      loadingIndicator.innerHTML =
        '<p class="text-danger">Laden des Feeds fehlgeschlagen.</p>';
    } finally {
      isLoading = false;
      if (!nextPage) {
        if (feedList.children.length === 0) {
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Es gibt noch keine Bewertungen.</p>';
        } else {
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        }
      } else {
        loadingIndicator.style.display = "none";
      }
    }
  }

  async function loadGenericFormtoModal(url, title) {
    modalTitle.textContent = "Lade...";
    modalBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    if (!bsModal._isShown) bsModal.show();
    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok)
        throw new Error("Formular konnte nicht geladen werden.");
      modalBody.innerHTML = await response.text();
      modalTitle.textContent = title;
    } catch (error) {
      modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
  }

  // Intersection Observer und initialer Aufruf
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoading) {
      loadFeedItems();
    }
  });

  if (trigger) {
    observer.observe(trigger);
    loadFeedItems();
  }
});
