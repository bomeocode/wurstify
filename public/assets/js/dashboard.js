// Wir importieren die Funktion, die wir für das Bewertungsformular benötigen.
import { initializeRatingFormScripts } from "./rating-form-handler.js";

document.addEventListener("DOMContentLoaded", function () {
  // === 1. SETUP & KARTEN-INITIALISIERUNG ===
  const mapContainer = document.getElementById("map-container");
  if (!mapContainer) return;

  const vendorsData = JSON.parse(mapContainer.dataset.ratings || "[]");
  let startZoom = 6,
    startCenter = [51.16, 10.45];

  const savedZoom = sessionStorage.getItem("mapZoom");
  const savedLat = sessionStorage.getItem("mapLat");
  const savedLng = sessionStorage.getItem("mapLng");
  if (savedZoom && savedLat && savedLng) {
    startZoom = parseInt(savedZoom, 10);
    startCenter = [parseFloat(savedLat), parseFloat(savedLng)];
  }

  const map = L.map("map").setView(startCenter, startZoom);

  const modalElement = document.getElementById("ajax-modal");
  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  let lazyLoadObserver;
  let lightbox;

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "© OpenStreetMap",
  }).addTo(map);

  map.on("zoomend moveend", () => {
    const center = map.getCenter();
    sessionStorage.setItem("mapZoom", map.getZoom());
    sessionStorage.setItem("mapLat", center.lat);
    sessionStorage.setItem("mapLng", center.lng);
  });

  const bratwurstIcon = L.icon({
    iconUrl: "/assets/img/bratwurst-icon.svg",
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -16],
  });

  const mobilIcon = L.icon({
    iconUrl: "/assets/img/bratwurst-icon-mobil.svg",
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -16],
  });

  const markers = L.markerClusterGroup({
    showCoverageOnHover: false,
    iconCreateFunction: function (cluster) {
      const childCount = cluster.getChildCount();
      let c = " marker-cluster-";
      if (childCount < 10) {
        c += "small";
      } else if (childCount < 100) {
        c += "medium";
      } else {
        c += "large";
      }
      return L.divIcon({
        html: "<div><span>" + childCount + "</span></div>",
        className: "marker-cluster" + c,
        iconSize: new L.Point(40, 40),
      });
    },
  });

  if (vendorsData.length > 0) {
    vendorsData.forEach((vendor) => {
      const iconToUse = vendor.category === "mobil" ? mobilIcon : bratwurstIcon;
      // console.log("Vendor-Category: " + vendor.category);
      const marker = L.marker([vendor.latitude, vendor.longitude], {
        icon: iconToUse,
      });
      const overallAvg =
        (parseFloat(vendor.avg_taste) +
          parseFloat(vendor.avg_appearance) +
          parseFloat(vendor.avg_presentation) +
          parseFloat(vendor.avg_price) +
          parseFloat(vendor.avg_service)) /
        5;
      marker.bindPopup(
        `<b>${vendor.vendor_name}</b><br><small>Gesamt: <b>${overallAvg.toFixed(
          1
        )} ★</b> (${
          vendor.total_ratings
        } Bewertungen)</small><div class="d-flex mt-2"><button type="button" class="btn btn-sm btn-primary flex-grow-1 me-1 open-vendor-modal" data-url="/vendor/${
          vendor.uuid
        }" data-vendor-uuid="${
          vendor.uuid
        }">Details</button><button type="button" class="btn btn-sm btn-success flex-grow-1 ms-1 open-modal-form" data-url="/ratings/new?vendor_uuid=${
          vendor.uuid
        }">Bewerten</button></div>`
      );
      markers.addLayer(marker);
    });
  }
  map.addLayer(markers);

  // === 2. ZENTRALE EVENT LISTENERS ===
  document.addEventListener("click", async function (e) {
    const voteButton = e.target.closest(".vote-button");
    if (voteButton) {
      e.preventDefault();

      // Button sofort sperren, um Doppelklicks zu verhindern
      voteButton.disabled = true;

      const ratingId = voteButton.dataset.ratingId;
      const countSpan = voteButton.querySelector(".badge");

      try {
        const csrfToken = document.querySelector(
          'meta[name="X-CSRF-TOKEN-VALUE"]'
        )?.content;
        if (!csrfToken) throw new Error("CSRF Token nicht gefunden.");

        const response = await fetch(`/api/ratings/${ratingId}/vote`, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
          },
        });
        const result = await response.json();
        if (!response.ok) throw result;

        // UI mit der Server-Antwort aktualisieren
        if (countSpan) countSpan.textContent = result.new_count;
        if (result.voted) {
          voteButton.classList.remove("btn-outline-success");
          voteButton.classList.add("btn-success");
        } else {
          voteButton.classList.remove("btn-success");
          voteButton.classList.add("btn-outline-success");
        }
      } catch (error) {
        // Ihre Toast-Funktion verwenden, falls vorhanden
        console.error("Fehler beim Abstimmen:", error);
        // displayToast(error.message || 'Abstimmung fehlgeschlagen', 'danger');
      } finally {
        voteButton.disabled = false;
      }
    }

    // NEU: Erkennt Klicks auf Benutzer-Links
    const userTrigger = e.target.closest(".open-user-modal");
    if (userTrigger) {
      e.preventDefault();
      // Wir verwenden unsere universelle Lade-Funktion
      loadContentIntoModal(userTrigger.dataset.url, "Benutzerprofil");
    }

    const detailButton = e.target.closest(".open-vendor-modal");
    if (detailButton) {
      e.preventDefault();
      // Rufe die neue, zentrale Funktion auf
      window.showOffcanvas(detailButton.dataset.url, (container) => {
        // Übergebe die Lazy-Loading-Initialisierung als Callback
        initializeLazyLoading(container, detailButton.dataset.vendorUuid);
      });
    }

    const formButton = e.target.closest(".open-modal-form");
    if (formButton) {
      e.preventDefault();
      loadFormIntoModal(formButton.dataset.url);
    }
  });

  modalElement.addEventListener("submit", async function (e) {
    if (e.target.tagName === "FORM" && e.target.closest("#ajax-modal")) {
      e.preventDefault();
      const form = e.target;
      const submitButton = form.querySelector('button[type="submit"]');
      if (!submitButton) return;
      const originalButtonText = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Speichere...';
      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const result = await response.json();
        if (!response.ok) throw result;
        displayToast(result.message || "Erfolgreich gespeichert.", "success");
        bsModal.hide();
        setTimeout(() => window.location.reload(), 1500);
      } catch (error) {
        const errorMessage = error.messages
          ? Object.values(error.messages)[0]
          : "Ein Fehler ist aufgetreten.";
        displayToast(errorMessage, "danger");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      }
    }
  });

  // === 3. HELFER-FUNKTIONEN ===

  function displayToast(message, type = "success") {
    const container = document.querySelector(".toast-container");
    if (!container) {
      alert(message);
      return;
    }
    const toastId = "toast-" + Date.now();
    const toastHTML = `<div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
    container.insertAdjacentHTML("beforeend", toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    toastElement.addEventListener("hidden.bs.toast", () =>
      toastElement.remove()
    );
  }

  async function loadContentIntoModal(url, title, onReadyCallback) {
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
      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = html;
      modalTitle.textContent =
        tempDiv.querySelector("h2,h1")?.textContent || title;
      if (typeof onReadyCallback === "function") {
        // Die CSRF-Daten werden für den Formular-Handler benötigt
        const csrfInput = modalBody.querySelector('form input[name^="csrf_"]');
        onReadyCallback(modalBody, csrfInput?.name, csrfInput?.value);
      }
    } catch (error) {
      modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
  }

  function loadVendorDetailsInModal(url, vendorUuid) {
    loadContentIntoModal(url, "Details", (container) => {
      initializeLazyLoading(container, vendorUuid);
    });
  }

  function loadFormIntoModal(url) {
    loadContentIntoModal(url, "Bratwurst bewerten", (container) => {
      // Wir rufen die IMPORTIERTE Funktion auf und übergeben ihr alles, was sie braucht.
      initializeRatingFormScripts(container, displayToast);
    });
  }

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
