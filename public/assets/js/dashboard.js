document.addEventListener("DOMContentLoaded", function () {
  const mapContainer = document.getElementById("map-container");
  const vendorsData = JSON.parse(mapContainer.dataset.ratings || "[]");

  // --- NEU (Teil 2): Gespeicherte Kartenposition laden ---
  let startZoom = 6;
  let startCenter = [51.16, 10.45]; // Standard: Mitte von Deutschland

  const savedZoom = sessionStorage.getItem("mapZoom");
  const savedLat = sessionStorage.getItem("mapLat");
  const savedLng = sessionStorage.getItem("mapLng");

  if (savedZoom && savedLat && savedLng) {
    startZoom = parseInt(savedZoom, 10);
    startCenter = [parseFloat(savedLat), parseFloat(savedLng)];
  }

  // 1. Leaflet-Karte mit der Startposition initialisieren
  const map = L.map("map").setView(startCenter, startZoom);

  // 2. Karten-Kacheln hinzufügen
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  // --- NEU (Teil 2): Kartenposition bei Änderung speichern ---
  map.on("zoomend moveend", function () {
    const center = map.getCenter();
    const zoom = map.getZoom();
    sessionStorage.setItem("mapZoom", zoom);
    sessionStorage.setItem("mapLat", center.lat);
    sessionStorage.setItem("mapLng", center.lng);
  });

  // --- NEU (Teil 1): Zum Nutzerstandort zoomen, ABER nur, wenn keine Position gespeichert war ---
  if (!savedZoom) {
    // Nur ausführen, wenn der Nutzer die Seite zum ersten Mal in der Session besucht
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const userLocation = [
            position.coords.latitude,
            position.coords.longitude,
          ];
          // Sanfter Flug zur neuen Position mit Zoom-Level 13
          map.flyTo(userLocation, 13);
        },
        (error) => {
          console.log(
            "Geolocation nicht verfügbar oder verweigert, bleibe bei Standardansicht."
          );
        }
      );
    }
  }

  // 3. Benutzerdefiniertes Icon (unverändert)
  const bratwurstIcon = L.icon({
    iconUrl: "assets/img/bratwurst-icon.svg",
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -16],
  });

  // 4. Marker-Cluster-Gruppe erstellen (unverändert)
  const markers = L.markerClusterGroup({
    showCoverageOnHover: false,
    iconCreateFunction: function (cluster) {
      // ... (Ihre Funktion zur Cluster-Darstellung bleibt unverändert) ...
      const childCount = cluster.getChildCount();
      let c = " marker-cluster-";
      if (childCount < 10) c += "small";
      else if (childCount < 100) c += "medium";
      else c += "large";
      return L.divIcon({
        html: "<div><span>" + childCount + "</span></div>",
        className: "marker-cluster" + c,
        iconSize: new L.Point(40, 40),
      });
    },
  });

  // 5. Marker hinzufügen (unverändert)
  if (vendorsData.length > 0) {
    vendorsData.forEach((vendor) => {
      const marker = L.marker([vendor.latitude, vendor.longitude], {
        icon: bratwurstIcon,
      });
      const overallAvg =
        (parseFloat(vendor.avg_taste) +
          parseFloat(vendor.avg_appearance) +
          parseFloat(vendor.avg_presentation) +
          parseFloat(vendor.avg_price) +
          parseFloat(vendor.avg_service)) /
        5;
      marker.bindPopup(
        `<b>${
          vendor.vendor_name
        }</b><br>Gesamtbewertung: <b>${overallAvg.toFixed(1)} ★</b> (${
          vendor.total_ratings
        } Bewertungen)<br><a href="/vendor/${vendor.uuid}">Details ansehen</a>`
      );
      markers.addLayer(marker);
    });
  }

  // 6. Cluster-Gruppe zur Karte hinzufügen (unverändert)
  map.addLayer(markers);
});
