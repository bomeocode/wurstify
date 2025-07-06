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
  document.addEventListener("click", function (e) {
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
      loadVendorDetailsInModal(
        detailButton.dataset.url,
        detailButton.dataset.vendorUuid
      );
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
  // Ersetzen Sie die bestehende initializeLazyLoading-Funktion in dashboard.js

  // Ersetzen Sie die bestehende initializeLazyLoading-Funktion in dashboard.js

  // In public/js/dashboard.js

  function initializeLazyLoading(container, vendorUuid) {
    const ratingsList = container.querySelector("#ratings-list");
    const loadingIndicator = container.querySelector("#loading-indicator");
    const trigger = container.querySelector("#load-more-trigger");
    if (!ratingsList || !loadingIndicator || !trigger) return;

    let nextPage = 1,
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

    async function loadRatings() {
      if (isLoading || !nextPage) return;
      isLoading = true;
      loadingIndicator.innerHTML =
        '<div class="spinner-border" role="status"></div>';
      loadingIndicator.style.display = "block";

      try {
        const response = await fetch(
          `/api/vendors/${vendorUuid}/ratings?page=${nextPage}`
        );
        const data = await response.json();

        if (data.ratings && data.ratings.length > 0) {
          data.ratings.forEach((rating) => {
            const el = document.createElement("div");
            el.className = "card shadow-sm mb-3";
            const avg =
              (parseFloat(rating.rating_taste) +
                parseFloat(rating.rating_appearance) +
                parseFloat(rating.rating_presentation) +
                parseFloat(rating.rating_price) +
                parseFloat(rating.rating_service)) /
              5;

            // HIER IST DIE WIEDERHERGESTELLTE, DETAILLIERTE ANSICHT
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
                        </div>`;
            ratingsList.appendChild(el);
          });

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
      } catch (e) {
        console.error(e);
      } finally {
        isLoading = false;
        if (!nextPage) {
          loadingIndicator.innerHTML =
            ratingsList.children.length === 0
              ? '<p class="text-muted text-center my-4">Für diesen Anbieter gibt es noch keine Bewertungen.</p>'
              : '<p class="text-muted text-center my-4">Ende der Bewertungen erreicht.</p>';
        } else {
          loadingIndicator.style.display = "none";
        }
      }
    }

    lazyLoadObserver = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !isLoading) {
          loadRatings();
        }
      },
      { root: modalElement, threshold: 0.1 }
    );
    if (trigger) {
      lazyLoadObserver.observe(trigger);
    }
  }
});
