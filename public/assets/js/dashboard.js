document.addEventListener("DOMContentLoaded", function () {
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
});
