import { showToast } from "./main.js";

document.addEventListener("DOMContentLoaded", function () {
  const userIdMeta = document.querySelector('meta[name="user-id"]');

  if (userIdMeta) {
    const userId = userIdMeta.content;
    const storageKey = `wurstify_feed_last_visit_${userId}`; // Derselbe benutzerspezifische Schlüssel

    // Verstecke das Badge
    const feedBadge = document.getElementById("feed-badge");
    if (feedBadge) feedBadge.style.display = "none";

    // Setze den Zeitstempel für DIESEN Benutzer
    localStorage.setItem(storageKey, new Date().toISOString());
  }

  const feedList = document.getElementById("feed-list");
  const loadingIndicator = document.getElementById("loading-indicator");
  const trigger = document.getElementById("load-more-trigger");
  if (!feedList) return; // Wenn wir nicht auf der Feed-Seite sind, nichts tun.

  // === SETUP FÜR DAS MODAL (wird jetzt für alle Aktionen gebraucht) ===
  const modalElement = document.getElementById("ajax-modal");
  if (!modalElement) return;
  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  let nextPage = 1;
  let isLoading = false;
  let lightbox;

  // === EVENT LISTENER (Jetzt für alle Modal-Trigger auf dieser Seite) ===
  document.addEventListener("click", async function (e) {
    // NEU: Erkennt Klicks auf Benutzer-Links
    const userTrigger = e.target.closest(".open-user-modal");
    if (userTrigger) {
      e.preventDefault();
      // Wir verwenden unsere universelle Lade-Funktion
      window.loadContentIntoModal(userTrigger.dataset.url, "Benutzerprofil");
    }

    // Fängt Klicks für "Weiterlesen", "Bewerten" und "Feedback" ab
    const triggerButton = e.target.closest(
      ".open-single-rating-modal, .open-modal-form"
    );

    if (triggerButton) {
      e.preventDefault();
      const url = triggerButton.dataset.url;
      const isRatingForm = triggerButton.classList.contains("open-modal-form");
      const isRatingDetail = triggerButton.classList.contains(
        "open-single-rating-modal"
      );

      // Titel bestimmen
      let title = "Information";
      if (isRatingForm) title = "Bratwurst bewerten";
      if (isRatingDetail) title = "Bewertung im Detail";
      if (url.includes("feedback")) title = "Feedback geben";

      // Modal laden und bei Bedarf die passenden Skripte initialisieren
      await loadFormIntoModal(url, title, (container) => {
        if (isRatingDetail) {
          initOrReloadLightbox();
        }
        if (isRatingForm && url.includes("/ratings/new")) {
          initializeRatingFormScripts(container, showToast);
        }
      });
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

  // === FUNKTIONEN (vollständig) ===

  // In public/js/feed.js
  async function loadFormIntoModal(url, title) {
    modalTitle.textContent = "Lade...";
    modalBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    if (!bsModal._isShown) bsModal.show();

    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok)
        throw new Error(`Server antwortete mit Status ${response.status}`);

      const html = await response.text();
      modalBody.innerHTML = html;
      modalTitle.textContent = title;

      // +++ HIER IST DIE MAGIE: DYNAMISCHER IMPORT +++
      // Wir importieren das Handler-Modul erst dann, wenn wir es wirklich brauchen.
      if (url.includes("/ratings/new")) {
        const handlerModule = await import("./rating-form-handler.js");
        // Wir rufen die exportierte Funktion aus dem geladenen Modul auf.
        handlerModule.initializeRatingFormScripts(modalBody, showToast);
      }
    } catch (error) {
      modalBody.innerHTML = `<div class="alert alert-danger">Inhalt konnte nicht geladen werden: ${error.message}</div>`;
    }
  }

  // === FUNKTIONEN ===
  function renderStars(score) {
    if (!score || score <= 0)
      return '<small class="text-muted">Nicht bewertet</small>';
    const s = Math.round(score);
    return "★".repeat(s) + "☆".repeat(5 - s);
  }

  // Fügen Sie diese beiden Funktionen zu Ihrer feed.js hinzu

  /**
   * Lädt die Details eines Anbieters und startet danach das Lazy-Loading für dessen Bewertungen.
   */
  async function loadVendorDetailsInModal(url, vendorUuid) {
    // Schritt 1: Nutze die globale Funktion, um den Haupt-Inhalt zu laden
    await window.loadContentIntoModal(url, "Anbieter-Details");

    // Schritt 2: Führe die spezifische Initialisierung für die Bewertungen aus
    const modalBody = document.getElementById("ajax-modal-body");
    if (modalBody) {
      initializeLazyLoading(modalBody, vendorUuid);
    }
  }

  /**
   * Initialisiert das "unendliche Scrollen" für Bewertungen innerhalb des Modals.
   */
  function initializeLazyLoading(container, vendorUuid) {
    const ratingsList = container.querySelector("#modal-ratings-list");
    const loadingIndicator = container.querySelector(
      "#modal-loading-indicator"
    );
    const loadMoreTrigger = container.querySelector("#modal-load-more-trigger");
    if (!ratingsList || !loadingIndicator || !loadMoreTrigger) return;

    let nextPage = 1;
    let isLoading = false;

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
            const el = document.createElement("div");
            el.className = "card mb-3";
            // Hier die HTML-Struktur für eine einzelne Bewertungs-Karte im Modal einfügen
            // (Sie können diese aus Ihrer dashboard.js kopieren)
            el.innerHTML = `... HTML für eine Bewertungs-Karte ...`;
            ratingsList.appendChild(el);
          });
          nextPage = data.pager.next_page_url ? nextPage + 1 : null;
        } else {
          nextPage = null;
        }
      } catch (error) {
        console.error("Fehler beim Laden der Bewertungen:", error);
      } finally {
        isLoading = false;
        loadingIndicator.style.display = "none";
        if (!nextPage) {
          loadMoreTrigger.innerHTML =
            '<p class="text-muted text-center small">Ende der Bewertungen erreicht.</p>';
        }
      }
    }

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          loadRatings();
        }
      },
      { root: document.getElementById("ajax-modal") }
    ); // Wichtig: Scroll-Container ist das Modal

    observer.observe(loadMoreTrigger);
    loadRatings(); // Initiales Laden der ersten Seite
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

          card.innerHTML = `
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-success vote-button" data-rating-id="${
                      rating.id
                    }">
                      <i class="bi bi-hand-thumbs-up"></i> Hilfreich
                      <span class="badge bg-success ms-1">${
                        rating.helpful_count
                      }</span>
                    </button>
                    <div>
                <h5 class="mb-0">
                <a href="#" class="text-decoration-none open-vendor-modal" data-url="/api/vendors/details/${
                  rating.vendor_uuid
                }" data-vendor-uuid="${rating.vendor_uuid}">
                    ${rating.vendor_name}
                </a>
                ${
                  rating.vendor_category === "mobil"
                    ? '<span class="badge bg-warning text-dark ms-2">Mobil</span>'
                    : ""
                }
           </h5>
           <small class="text-muted">${rating.vendor_address || ""}</small>
        </div>
        <div class="text-center ps-3">
            <h2 class="display-6 fw-bold mb-0">${avg.toFixed(1)}</h2>
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
                    <div class="card-footer text-muted d-flex justify-content-between align-items-center small py-2">
        <button type="button" class="btn btn-sm btn-warning open-modal-form" data-url="/ratings/new?vendor_uuid=${
          rating.uuid
        }">
            Diesen Anbieter bewerten
        </button>
        <div class="ms-2 text-end">
           <div class="d-flex align-items-center">
                <div class="me-2">
                    Bewertet von 
                    <a href="#" class="open-user-modal" data-url="/api/users/${
                      rating.user_id
                    }">
                        <strong>${rating.username || "Anonym"}</strong>
                    </a><br>
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
        </div>
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

  // Initialer Aufruf für Lazy Loading
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoading) {
      loadFeedItems();
    }
  });
  if (trigger) {
    observer.observe(trigger);
    loadFeedItems();
  }

  document.addEventListener("click", async function (e) {
    // Logik für das "Weiterlesen"-Modal (unverändert)
    // const detailTrigger = e.target.closest('.open-single-rating-modal');
    // if(detailTrigger) {
    //     // ... Ihr Code hierfür ...
    // }

    const vendorTrigger = e.target.closest(".open-vendor-modal");
    if (vendorTrigger) {
      e.preventDefault();
      const url = vendorTrigger.dataset.url;
      const vendorUuid = vendorTrigger.dataset.vendorUuid;
      // Wir rufen unsere neue, spezialisierte Funktion auf
      await loadVendorDetailsInModal(url, vendorUuid);
    }

    // Logik für den "Hilfreich"-Button
    const voteButton = e.target.closest(".vote-button");
    if (voteButton) {
      e.preventDefault();
      voteButton.disabled = true;

      const ratingId = voteButton.dataset.ratingId;
      const countSpan = voteButton.querySelector(".badge");

      try {
        // CSRF-Token aus den Meta-Tags lesen
        const csrfToken = document.querySelector(
          'meta[name="X-CSRF-TOKEN-VALUE"]'
        )?.content;
        if (!csrfToken) throw new Error("CSRF Token nicht gefunden.");

        const response = await fetch(`/api/ratings/${ratingId}/vote`, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken, // Token im Header mitsenden
          },
        });
        const result = await response.json();
        if (!response.ok) throw result;

        // UI aktualisieren
        countSpan.textContent = result.new_count;
        if (result.voted) {
          voteButton.classList.remove("btn-outline-success");
          voteButton.classList.add("btn-success");
        } else {
          voteButton.classList.remove("btn-success");
          voteButton.classList.add("btn-outline-success");
        }
      } catch (error) {
        console.error("Fehler beim Abstimmen:", error);
        // Fehler-Toast anzeigen, falls Sie eine showToast-Funktion haben
        // showToast(error.message || 'Abstimmung fehlgeschlagen', 'danger');
      } finally {
        voteButton.disabled = false;
      }
    }
  });

  // +++ NEU: Die komplette Lazy-Loading-Funktion für das VENDOR-MODAL +++
  function initializeLazyLoading(container, vendorUuid) {
    const ratingsList = container.querySelector("#ratings-list");
    const loadingIndicator = container.querySelector("#loading-indicator");
    const trigger = container.querySelector("#load-more-trigger");
    if (!ratingsList || !loadingIndicator || !trigger) return;

    let modalNextPage = 1;
    let modalIsLoading = false;

    // Wir leeren die Liste, um sicherzustellen, dass keine alten Bewertungen angezeigt werden
    ratingsList.innerHTML = "";

    async function loadModalRatings() {
      if (modalIsLoading || !modalNextPage) return;
      modalIsLoading = true;
      loadingIndicator.style.display = "block";

      try {
        const response = await fetch(
          `/api/vendors/${vendorUuid}/ratings?page=${modalNextPage}`
        );
        const data = await response.json();

        if (data.ratings && data.ratings.length > 0) {
          data.ratings.forEach((rating) => {
            const el = document.createElement("div");
            el.className = "card mb-3";
            // Hier Ihre vollständige, "schöne" HTML-Vorlage für eine Bewertungskarte einfügen
            el.innerHTML = `
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="${
                                          rating.avatar
                                            ? "/uploads/avatars/" +
                                              rating.avatar
                                            : "/assets/img/avatar-placeholder.png"
                                        }" alt="Avatar" class="avatar-image-md rounded-circle me-3">
                                        <div>
                                            <h6 class="card-title mb-0"><a href="#" class="open-user-modal" data-url="/api/users/${
                                              rating.user_id
                                            }">
                                                <strong>${
                                                  rating.username || "Anonym"
                                                }</strong>
                                            </a></h6>
                                            <small class="text-muted">schrieb am ${new Date(
                                              rating.created_at
                                            ).toLocaleDateString(
                                              "de-DE"
                                            )}</small>
                                        </div>
                                    </div>
                                    <p class="card-text fst-italic">"${
                                      rating.comment || "Kein Kommentar"
                                    }"</p>
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
                            <hr class="my-2">
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
                                ? `<div class="rating-images mt-3"><div class="row g-2">
                                ${
                                  rating.image1
                                    ? `<div class="col-3"><a href="/uploads/ratings/${rating.image1}" class="glightbox" data-gallery="vendor-${vendorUuid}-rating-${rating.id}"><img src="/uploads/ratings/${rating.image1}" class="img-fluid rounded" alt="Bild 1"></a></div>`
                                    : ""
                                }
                                ${
                                  rating.image2
                                    ? `<div class="col-3"><a href="/uploads/ratings/${rating.image2}" class="glightbox" data-gallery="vendor-${vendorUuid}-rating-${rating.id}"><img src="/uploads/ratings/${rating.image2}" class="img-fluid rounded" alt="Bild 2"></a></div>`
                                    : ""
                                }
                                ${
                                  rating.image3
                                    ? `<div class="col-3"><a href="/uploads/ratings/${rating.image3}" class="glightbox" data-gallery="vendor-${vendorUuid}-rating-${rating.id}"><img src="/uploads/ratings/${rating.image3}" class="img-fluid rounded" alt="Bild 3"></a></div>`
                                    : ""
                                }
                            </div></div>`
                                : ""
                            }
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between align-items-center small py-2">
        <button type="button" class="btn btn-sm btn-outline-success vote-button" data-rating-id="${
          rating.id
        }">
            <i class="bi bi-hand-thumbs-up"></i> Hilfreich
            <span class="badge bg-success ms-1">${
              rating.helpful_count || 0
            }</span>
        </button>
        <div class="ms-2 text-end">
           <div class="d-flex align-items-center">
                <div class="me-2">
                    Bewertet von <strong>${rating.username || "Anonym"}</strong>
                </div>
                <img src="${
                  rating.avatar
                    ? "/uploads/avatars/" + rating.avatar
                    : "/assets/img/avatar-placeholder.png"
                }" 
                     alt="Avatar" class="avatar-image-sm rounded-circle">
           </div>
        </div>
    </div>`;
            ratingsList.appendChild(el);
          });

          //initOrReloadLightbox();
          modalNextPage =
            data.pager.currentPage < data.pager.pageCount
              ? data.pager.currentPage + 1
              : null;
        } else {
          modalNextPage = null;
        }
      } catch (e) {
        console.error("Fehler beim Laden der Modal-Bewertungen:", e);
      } finally {
        modalIsLoading = false;
        if (!modalNextPage) {
          loadingIndicator.innerHTML =
            '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        } else {
          loadingIndicator.style.display = "none";
        }
      }
    }

    const modalObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !modalIsLoading) {
          loadModalRatings();
        }
      },
      { root: modalElement }
    ); // Wichtig: Beobachtet das Scrollen innerhalb des Modals

    modalObserver.observe(trigger);
    loadModalRatings(); // Erste Seite der Modal-Bewertungen laden
  }

  if (trigger) {
    observer.observe(trigger);
    loadFeedItems();
  }
});
