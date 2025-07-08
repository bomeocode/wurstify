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
  let lazyLoadObserver;

  // === EVENT LISTENER (Jetzt für alle Modal-Trigger auf dieser Seite) ===
  document.addEventListener("click", async function (e) {
    // NEU: Erkennt Klicks auf Benutzer-Links
    const userTrigger = e.target.closest(".open-user-modal");
    if (userTrigger) {
      e.preventDefault();
      // Wir verwenden unsere universelle Lade-Funktion
      window.loadContentIntoModal(userTrigger.dataset.url, "Benutzerprofil");
    }

    const claimTrigger = e.target.closest(".open-claim-form");
    if (claimTrigger) {
      e.preventDefault();
      window.showOffcanvas(claimTrigger.dataset.url);
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
    const ratingsList = container.querySelector("#ratings-list");
    const loadingIndicator = container.querySelector("#loading-indicator");
    const trigger = container.querySelector("#load-more-trigger");
    if (!ratingsList || !loadingIndicator || !trigger) return;

    let modalNextPage = 1,
      isLoading = false,
      lightbox;
    if (lazyLoadObserver) lazyLoadObserver.disconnect();
    ratingsList.innerHTML = "";

    function renderStars(score) {
      if (!score || score <= 0)
        return '<small class="text-muted">Nicht bewertet</small>';
      const s = Math.round(score);
      return "★".repeat(s) + "☆".repeat(5 - s);
    }

    async function loadModalRatings() {
      if (isLoading || !modalNextPage) return;
      isLoading = true;
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

            // +++ HIER IST DIE FEHLENDE ZEILE +++
            const avg =
              (parseFloat(rating.rating_taste) +
                parseFloat(rating.rating_appearance) +
                parseFloat(rating.rating_presentation) +
                parseFloat(rating.rating_price) +
                parseFloat(rating.rating_service)) /
              5;

            // Der Rest des innerHTML kann nun auf 'avg' zugreifen
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
                                            <h6 class="card-title mb-0"><strong>${
                                              rating.username || "Anonym"
                                            }</strong></h6>
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
                            </div>`;
            ratingsList.appendChild(el);
          });

          if (typeof GLightbox === "function") {
            if (lightbox) lightbox.reload();
            else {
              lightbox = GLightbox({ selector: ".glightbox" });
            }
          }
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
        isLoading = false;
        if (!modalNextPage) {
          loadingIndicator.innerHTML =
            ratingsList.children.length === 0
              ? '<p class="text-muted text-center my-4">Für diesen Anbieter gibt es noch keine Bewertungen.</p>'
              : '<p class="text-muted text-center my-4">Ende der Bewertungen.</p>';
        } else {
          loadingIndicator.style.display = "none";
        }
      }
    }

    const modalObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !isLoading) {
          loadModalRatings();
        }
      },
      { root: modalElement }
    );

    modalObserver.observe(trigger);
    loadModalRatings();
  }

  // In public/js/feed.js

  async function loadFeedItems() {
    if (isLoading || !nextPage) return;
    isLoading = true;
    loadingIndicator.innerHTML =
      '<div class="spinner-border" role="status"></div>';
    loadingIndicator.style.display = "block";

    try {
      const response = await fetch(`/api/feed/ratings?page=${nextPage}`);
      const data = await response.json();

      if (data.ratings_html && data.ratings_html.length > 0) {
        data.ratings_html.forEach((html) => {
          feedList.insertAdjacentHTML("beforeend", html);
        });
        // Wir initialisieren GLightbox neu oder laden die bestehende Instanz neu
        if (typeof GLightbox === "function") {
          if (window.lightboxInstance) {
            window.lightboxInstance.reload();
          } else {
            window.lightboxInstance = GLightbox({
              selector: ".glightbox",
              touchNavigation: true,
              loop: false,
            });
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
        loadingIndicator.innerHTML =
          feedList.children.length === 0
            ? '<p class="text-muted text-center my-4">Es gibt noch keine Bewertungen.</p>'
            : '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
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

      const pageLightbox = window.lightboxInstance;

      // Wenn das Offcanvas geschlossen wird, zerstören wir die Lightbox
      if (pageLightbox) {
        const offcanvasEl = document.getElementById("ajax-offcanvas");
        offcanvasEl.addEventListener(
          "hidden.bs.offcanvas",
          () => {
            pageLightbox.destroy();
            window.lightboxInstance = null; // Instanz zurücksetzen
          },
          { once: true }
        );
      }
      window.showOffcanvas(vendorTrigger.dataset.url, (container) => {
        initializeLazyLoading(container, vendorTrigger.dataset.vendorUuid);
      });
    }
  });

  // +++ NEU: Die komplette Lazy-Loading-Funktion für das VENDOR-MODAL +++
  // In public/js/feed.js

  function initializeLazyLoading(container, vendorUuid) {
    // Dieser Teil ist für das MODAL, das vom FEED aus geöffnet wird.
    const ratingsList = container.querySelector("#ratings-list");
    const loadingIndicator = container.querySelector("#loading-indicator");
    const trigger = container.querySelector("#load-more-trigger");
    if (!ratingsList || !loadingIndicator || !trigger) {
      console.error("Lazy-Loading-Elemente im Modal nicht gefunden!");
      return;
    }

    let modalNextPage = 1;
    let modalIsLoading = false;
    // Die 'lightbox'-Variable sollte im Haupt-Scope der feed.js deklariert sein.

    if (lazyLoadObserver) lazyLoadObserver.disconnect();
    ratingsList.innerHTML = "";

    async function loadModalRatings() {
      if (isLoading || !modalNextPage) return;
      isLoading = true;
      loadingIndicator.style.display = "block";

      try {
        const response = await fetch(
          `/api/vendors/${vendorUuid}/ratings?page=${modalNextPage}`
        );
        const data = await response.json();

        if (data.ratings_html && data.ratings_html.length > 0) {
          // Statt innerHTML zu bauen, fügen wir das fertige HTML einfach ein
          data.ratings_html.forEach((html) => {
            ratingsList.insertAdjacentHTML("beforeend", html);
          });

          // GLightbox neu initialisieren
          if (typeof GLightbox === "function") {
            if (lightbox) lightbox.reload();
            else {
              lightbox = GLightbox({ selector: ".glightbox" });
            }
          }

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
        isLoading = false;
        if (!modalNextPage) {
          loadingIndicator.innerHTML =
            ratingsList.children.length === 0
              ? '<p class="text-muted text-center my-4">Für diesen Anbieter gibt es noch keine Bewertungen.</p>'
              : '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        } else {
          loadingIndicator.style.display = "none";
        }
      }
    }

    const modalObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !isLoading) {
          loadModalRatings();
        }
      },
      { root: document.getElementById("ajax-offcanvas") }
    ); // Wichtig: Beobachtet das Scrollen im Offcanvas

    if (trigger) {
      modalObserver.observe(trigger);
      loadModalRatings();
    }
  }
});
