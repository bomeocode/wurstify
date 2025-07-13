// ========================================================
// === Globale app.js - Finale, stabile Version
// ========================================================

import { initializeRatingFormScripts } from "./rating-form-handler.js";

let lightboxInstance; // Globale Variable, um die Instanz zu speichern

window.initOrReloadLightbox = function () {
  if (typeof GLightbox !== "function") {
    console.error("GLightbox nicht geladen.");
    return;
  }

  if (lightboxInstance) {
    lightboxInstance.reload();
  } else {
    lightboxInstance = GLightbox({
      selector: ".glightbox",
      touchNavigation: true,
      loop: false,
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

document.addEventListener("alpine:init", () => {
  // -- Komponente für die Vendor-Detailansicht (mit Lazy Loading & Lightbox) --
  Alpine.data("vendorDetailComponent", (initialData) => ({
    uuid: initialData.vendor.uuid,
    ratingsHtml: initialData.ratings_html || [],
    isLoading: false,
    nextPage:
      initialData.pager.currentPage < initialData.pager.pageCount
        ? initialData.pager.currentPage + 1
        : null,
    ratingsLoaded: initialData.ratings_html.length > 0,

    loadMoreRatings() {
      if (this.isLoading || !this.nextPage) return;
      this.isLoading = true;
      fetch(`/api/vendors/${this.uuid}/ratings?page=${this.nextPage}`)
        .then((res) => res.json())
        .then((data) => {
          this.ratingsHtml = this.ratingsHtml.concat(data.ratings_html);
          this.nextPage =
            data.pager.currentPage < data.pager.pageCount
              ? data.pager.currentPage + 1
              : null;
          this.$nextTick(() => {
            window.initOrReloadLightbox();
          });
        })
        .catch((err) =>
          window.showToast(
            "Bewertungen konnten nicht geladen werden.",
            "danger"
          )
        )
        .finally(() => (this.isLoading = false));
    },
    init() {
      this.$nextTick(() => {
        window.initOrReloadLightbox();
      });
    },
  }));
});

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
      this.loading = true;
      const csrfHeader = document.querySelector(
        'meta[name="X-CSRF-TOKEN-NAME"]'
      )?.content;
      const csrfToken = document.querySelector(
        'meta[name="X-CSRF-TOKEN-VALUE"]'
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

window.vendorDetailComponent = function (initialData) {
  return {
    // === ZUSTAND (State) ===
    // Wir speichern das ganze Vendor-Objekt
    vendor: initialData.vendor,
    isLoading: false,
    ratingsHtml: initialData.initialRatingsHtml || [],
    nextPage:
      initialData.initialPager.currentPage < initialData.initialPager.pageCount
        ? initialData.initialPager.currentPage + 1
        : null,
    ratingsLoaded: initialData.initialRatingsHtml.length > 0,

    // === METHODE zum Nachladen ===
    loadMoreRatings() {
      if (this.isLoading || !this.nextPage) return;
      this.isLoading = true;

      // KORREKTUR: Wir verwenden jetzt this.vendor.uuid
      fetch(`/api/vendors/${this.vendor.uuid}/ratings?page=${this.nextPage}`)
        .then((response) => response.json())
        .then((data) => {
          this.ratingsHtml = this.ratingsHtml.concat(data.ratings_html);
          this.nextPage =
            data.pager.currentPage < data.pager.pageCount
              ? data.pager.currentPage + 1
              : null;
          this.isLoading = false;
          this.$nextTick(() => {
            if (window.initOrReloadLightbox) window.initOrReloadLightbox();
          });
        })
        .catch(() => {
          this.isLoading = false;
          window.showToast(
            "Bewertungen konnten nicht geladen werden.",
            "danger"
          );
        });
    },

    // === INIT-METHODE ===
    init() {
      // Lightbox für die initialen Bilder aufrufen
      this.$nextTick(() => {
        if (window.initOrReloadLightbox) window.initOrReloadLightbox();
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

    if (url.includes("/ratings/new")) {
      initializeRatingFormScripts(bodyEl, window.showToast);
    }

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

// Dieser Listener wird einmal beim Laden der Seite initialisiert.
document.addEventListener("DOMContentLoaded", function () {
  console.log("Globale app.js geladen.");

  // Zentraler Klick-Listener, der nur noch die Overlays öffnet
  document.addEventListener("click", function (e) {
    const trigger = e.target.closest(
      ".open-modal, .open-offcanvas, .open-claim-form"
    );
    if (trigger) {
      e.preventDefault();
      const type = trigger.classList.contains("open-offcanvas")
        ? "offcanvas"
        : "modal";
      const url = trigger.dataset.url;
      const title = trigger.title || "Information";

      loadInOverlay(type, url, title);
    }
  });

  // Globaler Submit-Handler für Formulare in Modals/Offcanvas
  document.addEventListener("submit", async function (e) {
    const form = e.target.closest("#ajax-modal form, #ajax-offcanvas form");
    if (form) {
      e.preventDefault();

      const submitButton = form.querySelector('button[type="submit"]');
      let originalButtonText = "Senden";

      if (submitButton) {
        originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML =
          '<span class="spinner-border spinner-border-sm"></span> Sende...';
      }

      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        const result = await response.json();
        if (!response.ok) {
          // Wirft den Fehler, damit wir ihn im catch-Block behandeln können
          throw result;
        }

        // Erfolgsfall
        window.showToast(result.message || "Aktion erfolgreich.", "success");

        // Overlay schließen
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("ajax-modal")
        );
        const offcanvas = bootstrap.Offcanvas.getInstance(
          document.getElementById("ajax-offcanvas")
        );
        if (modal && modal._isShown) modal.hide();
        if (offcanvas && offcanvas._isShown) offcanvas.hide();

        // Wenn die Antwort eine URL zur Weiterleitung enthält, führen wir sie aus
        if (result.redirect_url) {
          setTimeout(() => {
            window.location.href = result.redirect_url;
          }, 1000);
        }
      } catch (error) {
        // Fehlerfall (z.B. Validierung)
        const errorMessage =
          error && error.messages
            ? Object.values(error.messages)[0]
            : "Ein unbekannter Fehler ist aufgetreten.";
        window.showToast(errorMessage, "danger");
      } finally {
        // Button in jedem Fall wiederherstellen
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      }
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
