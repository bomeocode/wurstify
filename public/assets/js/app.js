// ========================================================
// === Globale app.js - Finale, stabile Version
// ========================================================

let lightboxInstance; // Globale Variable, um die Instanz zu speichern

/**
 * Initialisiert eine neue GLightbox-Instanz oder lädt eine bestehende neu.
 * Das stellt sicher, dass auch nachgeladene Bilder den Lightbox-Effekt erhalten.
 */
window.initOrReloadLightbox = function () {
  // Führe nichts aus, wenn die GLightbox-Bibliothek nicht geladen ist.
  if (typeof GLightbox !== "function") {
    return;
  }

  // Wenn schon eine Instanz existiert, lade sie neu.
  if (lightboxInstance) {
    lightboxInstance.reload();
  } else {
    // Ansonsten erstelle eine neue Instanz.
    lightboxInstance = GLightbox({
      selector: ".glightbox",
    });
  }
};

/**
 * Zeigt eine globale Toast-Nachricht an.
 */
window.showToast = function (message, type = "info") {
  const container = document.querySelector(".toast-container");
  if (!container) {
    alert(message);
    return;
  }
  const toastId = "toast-" + Date.now();
  const toastHTML = `<div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
  container.insertAdjacentHTML("beforeend", toastHTML);
  const toastElement = document.getElementById(toastId);
  if (toastElement) {
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    toastElement.addEventListener("hidden.bs.toast", () =>
      toastElement.remove()
    );
  }
};

/**
 * Erstellt das Datenobjekt für eine Alpine.js ratingCard Komponente.
 * @param {object} initialData - Die Daten für die Bewertung aus PHP.
 */
window.ratingCard = function (initialData) {
  return {
    // Daten aus PHP übernehmen
    id: initialData.id,
    comment: initialData.comment,
    helpful_count: initialData.helpful_count,
    user_has_voted: initialData.user_has_voted,
    user: initialData.user,
    vendor: initialData.vendor,
    avg: initialData.avg,
    details: initialData.details,
    images: initialData.images,

    // Lokaler Zustand für diese Karte
    expanded: false,
    loading: false,

    // Berechnete Eigenschaften für die Anzeige
    get needsReadMore() {
      return this.comment && this.comment.length > 150;
    },
    get shortComment() {
      if (!this.comment) return "";
      return this.needsReadMore
        ? this.comment.substring(0, 150).replace(/\s+\S*$/, "...")
        : this.comment;
    },
    get fullComment() {
      return (this.comment || "").replace(/\n/g, "<br>");
    },

    // Methoden
    renderStars(score) {
      if (!score || score <= 0) return "";
      const s = Math.round(score);
      return "★".repeat(s) + "☆".repeat(5 - s);
    },
    toggleVote() {
      if (!"<?= auth()->logged() ?>") {
        // Diese PHP-Prüfung funktioniert hier nicht, muss im Backend erfolgen.
        window.showToast("Bitte einloggen, um abzustimmen.", "info");
        // Hier könnte man zur Login-Seite leiten: window.location.href = '/login';
        return;
      }
      this.loading = true;
      const csrfToken = document.querySelector(
        "meta[name='csrf-token']"
      )?.content;
      const csrfHeader = document.querySelector(
        "meta[name='csrf-header']"
      )?.content;
      let headers = {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      };
      if (csrfToken && csrfHeader) headers[csrfHeader] = csrfToken;

      fetch(`/api/ratings/${this.id}/vote`, {
        method: "POST",
        headers: headers,
      })
        .then((res) => {
          if (!res.ok) throw new Error("Fehler bei der Abstimmung");
          return res.json();
        })
        .then((data) => {
          this.helpful_count = data.new_count;
          this.user_has_voted = data.voted;
        })
        .catch((err) =>
          window.showToast("Fehler bei der Abstimmung.", "danger")
        )
        .finally(() => (this.loading = false));
    },
  };
};

// In public/js/app.js

window.vendorDetailComponent = function (initialData) {
  return {
    // Daten aus PHP übernehmen
    vendorUuid: initialData.uuid,

    // Lokaler Zustand für diese Komponente
    isLoading: false,
    ratingsLoaded: initialData.initialRatingsHtml.length > 0,

    // Pager-Initialisierung
    nextPage:
      initialData.initialPager.currentPage < initialData.initialPager.pageCount
        ? initialData.initialPager.currentPage + 1
        : null,

    // Die Methode zum Nachladen
    loadMoreRatings() {
      if (this.isLoading || !this.nextPage) return;
      this.isLoading = true;

      fetch(`/api/vendors/${this.vendorUuid}/ratings?page=${this.nextPage}`)
        .then((response) => response.json())
        .then((data) => {
          const ratingsList = document.getElementById("modal-ratings-list");
          if (ratingsList && data.ratings_html) {
            ratingsList.insertAdjacentHTML(
              "beforeend",
              data.ratings_html.join("")
            );
          }

          window.initOrReloadLightbox();

          this.nextPage =
            data.pager.currentPage < data.pager.pageCount
              ? data.pager.currentPage + 1
              : null;

          this.isLoading = false;
        });
    },
  };
};

/**
 * Globale Funktion zum Laden von Inhalten in ein Overlay.
 */
window.loadInOverlay = async function (type, url, title, onReadyCallback) {
  const overlayEl = document.getElementById(
    type === "modal" ? "ajax-modal" : "ajax-offcanvas"
  );
  if (!overlayEl) return;

  const titleEl = overlayEl.querySelector(".modal-title, .offcanvas-title");
  const bodyEl = overlayEl.querySelector(".modal-body, .offcanvas-body");
  const bsInstance =
    type === "modal"
      ? bootstrap.Modal.getOrCreateInstance(overlayEl)
      : bootstrap.Offcanvas.getOrCreateInstance(overlayEl);

  titleEl.textContent = "Lade...";
  bodyEl.innerHTML =
    '<div class="text-center p-5"><div class="spinner-border"></div></div>';
  if (!bsInstance._isShown) bsInstance.show();

  try {
    const response = await fetch(url, {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    if (!response.ok)
      throw new Error(`Server antwortete mit Status ${response.status}`);

    bodyEl.innerHTML = await response.text();
    titleEl.textContent = bodyEl.querySelector("h1, h2")?.textContent || title;

    if (typeof GLightbox === "function") {
      if (lightboxInstance) {
        lightboxInstance.reload();
      } else {
        lightboxInstance = GLightbox({ selector: ".glightbox" });
      }
    }

    if (typeof onReadyCallback === "function") {
      onReadyCallback(bodyEl);
    }
  } catch (error) {
    bodyEl.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
  }
};

/**
 * Globale Funktion zum Lazy-Loading von Bewertungen im Offcanvas.
 */
window.initializeLazyLoading = function (container, vendorUuid) {
  const ratingsList = container.querySelector("#ratings-list");
  const loadingIndicator = container.querySelector("#loading-indicator");
  const trigger = container.querySelector("#load-more-trigger");
  if (!ratingsList || !loadingIndicator || !trigger) return;

  let nextPage = 1,
    isLoading = false,
    lightbox;
  ratingsList.innerHTML = "";

  async function loadRatings() {
    if (isLoading || !nextPage) return;
    isLoading = true;
    loadingIndicator.style.display = "block";
    try {
      const response = await fetch(
        `/api/vendors/${vendorUuid}/ratings?page=${nextPage}`
      );
      const data = await response.json();
      if (data.ratings_html && data.ratings_html.length > 0) {
        data.ratings_html.forEach((html) =>
          ratingsList.insertAdjacentHTML("beforeend", html)
        );
        nextPage =
          data.pager.currentPage < data.pager.pageCount
            ? data.pager.currentPage + 1
            : null;
      } else {
        nextPage = null;
      }
    } catch (e) {
      console.error("Fehler beim Laden der Bewertungen:", e);
    } finally {
      isLoading = false;
      if (!nextPage) {
        loadingIndicator.innerHTML =
          ratingsList.children.length === 0
            ? '<p class="text-muted text-center my-4">Noch keine Bewertungen.</p>'
            : '<p class="text-muted text-center my-4">Ende erreicht.</p>';
      } else {
        loadingIndicator.style.display = "none";
      }
    }
  }

  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && !isLoading) loadRatings();
    },
    { root: container.closest(".offcanvas") }
  );

  observer.observe(trigger);
  loadRatings();
};

// Dieser Listener wird einmal beim Laden der Seite initialisiert.
document.addEventListener("DOMContentLoaded", function () {
  console.log("Globale app.js geladen und bereit.");

  // Zentraler Klick-Listener, der die Overlays steuert
  document.addEventListener("click", function (e) {
    // Test-Zeile: Schreibt bei JEDEM Klick etwas in die Konsole
    // console.log("Ein Klick wurde im Dokument registriert.");

    const trigger = e.target.closest(".open-modal, .open-offcanvas");
    if (trigger) {
      // console.log("Trigger-Element gefunden:", trigger);
      e.preventDefault();

      const type = trigger.classList.contains("open-offcanvas")
        ? "offcanvas"
        : "modal";
      const url = trigger.dataset.url;
      const title = trigger.title || "Information";
      const callbackName = trigger.dataset.initCallback;
      const vendorUuid = trigger.dataset.vendorUuid;

      const onReadyCallback =
        callbackName && typeof window[callbackName] === "function"
          ? (container) => window[callbackName](container, vendorUuid)
          : null;

      window.loadInOverlay(type, url, title, onReadyCallback);
    }
  });

  // Globaler Submit-Handler für Formulare in Modals/Offcanvas
  document.addEventListener("submit", async function (e) {
    const form = e.target.closest("#ajax-modal form, #ajax-offcanvas form");
    if (form) {
      e.preventDefault();
      // ... Ihr vollständiger AJAX-Submit-Code ...
    }
  });

  if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
      navigator.serviceWorker
        .register("/sw.js")
        .then((registration) => {
          console.log("Service Worker: Registrierung erfolgreich.");

          const showUpdatePrompt = (worker) => {
            const updatePrompt = document.getElementById("update-prompt");
            const reloadButton = document.getElementById("reload-button");

            if (updatePrompt && reloadButton) {
              updatePrompt.style.display = "flex";
              reloadButton.onclick = () => {
                // Der Button sendet jetzt NUR noch die Nachricht.
                worker.postMessage({
                  type: "SKIP_WAITING",
                });
                // Wir geben dem Nutzer sofort visuelles Feedback.
                updatePrompt.innerHTML =
                  '<span>Update wird installiert...</span><div class="spinner-border spinner-border-sm ms-2" role="status"></div>';
              };
            }
          };

          // Prüfen, ob bereits ein Worker wartet
          if (registration.waiting) {
            showUpdatePrompt(registration.waiting);
          }

          // Lauschen auf zukünftige Updates
          registration.onupdatefound = () => {
            const installingWorker = registration.installing;
            if (installingWorker) {
              installingWorker.onstatechange = () => {
                if (
                  installingWorker.state === "installed" &&
                  navigator.serviceWorker.controller
                ) {
                  showUpdatePrompt(installingWorker);
                }
              };
            }
          };
        })
        .catch((error) => {
          console.log("Service Worker Registrierung fehlgeschlagen:", error);
        });

      let refreshing;
      navigator.serviceWorker.addEventListener("controllerchange", () => {
        if (refreshing) return;
        window.location.reload();
        refreshing = true;
      });
    });
  }

  // Lädt Vendor-Details ins Offcanvas
  window.loadVendorDetailsInOffcanvas = async function (url, vendorUuid) {
    const offcanvasEl = document.getElementById("ajax-offcanvas");
    if (!offcanvasEl) return;
    const offcanvasTitle = offcanvasEl.querySelector(".offcanvas-title");
    const offcanvasBody = offcanvasEl.querySelector(".offcanvas-body");
    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

    offcanvasTitle.textContent = "Lade...";
    offcanvasBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    bsOffcanvas.show();

    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok) throw new Error("Inhalt konnte nicht geladen werden.");
      const html = await response.text();
      offcanvasBody.innerHTML = html;
      offcanvasTitle.textContent =
        offcanvasBody.querySelector("h1")?.textContent || "Details";

      // Starte das Lazy-Loading für die Bewertungen
      initializeLazyLoading(offcanvasBody, vendorUuid);
    } catch (error) {
      offcanvasBody.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
    }
  };
});
