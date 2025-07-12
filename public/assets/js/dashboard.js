// Wir importieren den Formular-Handler, da dessen Logik ausgelagert ist.
import { initializeRatingFormScripts } from "./rating-form-handler.js";

document.addEventListener("DOMContentLoaded", function () {
  console.log("Dashboard Skript (FINALE, VOLLSTÄNDIGE VERSION) geladen.");

  // === 1. SETUP & VARIABLEN ===
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
  const offcanvasElement = document.getElementById("ajax-offcanvas");
  if (!modalElement || !offcanvasElement) return;

  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const offcanvasTitle = offcanvasElement.querySelector(".offcanvas-title");
  const offcanvasBody = offcanvasElement.querySelector(".offcanvas-body");

  const bsModal = new bootstrap.Modal(modalElement);
  const bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);

  let lazyLoadObserver;

  // === 2. KARTEN-INITIALISIERUNG & POPUPS ===
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
        html: `<div><span>${childCount}</span></div>`,
        className: "marker-cluster" + c,
        iconSize: new L.Point(40, 40),
      });
    },
  });

  if (vendorsData.length > 0) {
    vendorsData.forEach((vendor) => {
      const iconToUse = vendor.category === "mobil" ? mobilIcon : bratwurstIcon;
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
        )} ★</b> (${vendor.total_ratings} Bewertungen)</small>` +
          `<div class="d-flex mt-2">` +
          `<button type="button" class="btn btn-sm btn-primary flex-grow-1 me-1 open-offcanvas" data-url="/api/vendors/details/${vendor.uuid}" data-vendor-uuid="${vendor.uuid}" title="Details für ${vendor.vendor_name}">Details</button>` +
          `<button type="button" class="btn btn-sm btn-success flex-grow-1 ms-1 open-modal" data-url="/ratings/new?vendor_uuid=${vendor.uuid}" title="Bewerte ${vendor.vendor_name}">Bewerten</button>` +
          `</div>`
      );
      markers.addLayer(marker);
    });
  }
  map.addLayer(markers);

  // === 3. ZENTRALER EVENT LISTENER ===
  document.addEventListener("click", async function (e) {
    const offcanvasTrigger = e.target.closest(".open-offcanvas");
    if (offcanvasTrigger) {
      e.preventDefault();
      await loadVendorDetailsInOffcanvas(
        offcanvasTrigger.dataset.url,
        offcanvasTrigger.dataset.vendorUuid
      );
    }

    const modalTrigger = e.target.closest(".open-modal");
    if (modalTrigger) {
      e.preventDefault();
      await loadFormIntoModal(modalTrigger.dataset.url);
    }

    const voteButton = e.target.closest(".vote-button");
    if (voteButton) {
      e.preventDefault();
      voteButton.disabled = true;
      const ratingId = voteButton.dataset.ratingId;
      const countSpan = voteButton.querySelector(".badge");
      try {
        const csrfToken = document.querySelector(
          'meta[name="X-CSRF-TOKEN-VALUE"]'
        )?.content;
        const response = await fetch(`/api/ratings/${ratingId}/vote`, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
          },
        });
        const result = await response.json();
        if (!response.ok) throw result;
        if (countSpan) countSpan.textContent = result.new_count;
        voteButton.classList.toggle("btn-success", result.voted);
        voteButton.classList.toggle("btn-outline-success", !result.voted);
      } catch (error) {
        console.error("Fehler beim Abstimmen:", error);
      } finally {
        voteButton.disabled = false;
      }
    }
  });

  modalElement.addEventListener("submit", async function (e) {
    if (e.target.tagName !== "FORM" || !e.target.closest("#ajax-modal")) return;
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
  });

  // === 4. HELFER-FUNKTIONEN ===

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

  async function loadVendorDetailsInOffcanvas(url, vendorUuid) {
    offcanvasTitle.textContent = "Lade Details...";
    offcanvasBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    bsOffcanvas.show();
    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok)
        throw new Error(`Server antwortete mit Status ${response.status}`);
      const html = await response.text();
      offcanvasBody.innerHTML = html;
      offcanvasTitle.textContent =
        offcanvasBody.querySelector("h1, h2")?.textContent || "Details";
      initializeLazyLoading(offcanvasBody, vendorUuid);
    } catch (error) {
      offcanvasBody.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
    }
  }

  async function loadFormIntoModal(url) {
    modalTitle.textContent = "Lade Formular...";
    modalBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    bsModal.show();
    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok)
        throw new Error(`Server antwortete mit Status ${response.status}`);
      const html = await response.text();
      modalBody.innerHTML = html;
      modalTitle.textContent =
        modalBody.querySelector("h1, h2")?.textContent || "Formular";
      if (url.includes("/ratings/new")) {
        initializeRatingFormScripts(modalBody, displayToast);
      }
    } catch (error) {
      modalBody.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
    }
  }
});
