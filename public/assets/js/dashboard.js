document.addEventListener("DOMContentLoaded", function () {
  const mapContainer = document.getElementById("map-container");
  // Wir benennen die Variable um, um die Klarheit zu erhöhen
  const vendorsData = JSON.parse(mapContainer.dataset.ratings || "[]");

  const map = L.map("map").setView([51.16, 10.45], 6);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "© OpenStreetMap",
  }).addTo(map);

  const bratwurstIcon = L.icon({
    iconUrl: "assets/img/bratwurst-icon.svg", // <-- Der entscheidende Pfad zu Ihrem Icon
    iconSize: [32, 32], // Die Größe des Icons in Pixeln [Breite, Höhe]
    iconAnchor: [16, 16], // Der Punkt des Icons, der auf der exakten Koordinate liegt (hier: die Mitte)
    popupAnchor: [0, -16], // Verschiebt das Popup, damit es mittig über dem Icon erscheint
  });
  const markers = L.markerClusterGroup();

  if (vendorsData.length > 0) {
    vendorsData.forEach((vendor) => {
      // Die Gesamtbewertung als Durchschnitt aller Kategorien berechnen
      const overallAvg =
        (parseFloat(vendor.avg_taste) +
          parseFloat(vendor.avg_appearance) +
          parseFloat(vendor.avg_presentation) +
          parseFloat(vendor.avg_price) +
          parseFloat(vendor.avg_service)) /
        5;

      const marker = L.marker([vendor.latitude, vendor.longitude], {
        icon: bratwurstIcon,
      });

      // Popup mit den neuen Durchschnitts-Daten und dem Link zur Detailseite
      marker.bindPopup(`
                <b>${vendor.vendor_name}</b><br>
                Gesamtbewertung: <b>${overallAvg.toFixed(1)} ★</b> (${
        vendor.total_ratings
      } Bewertungen)<br>
                <a href="/vendor/${vendor.uuid}">Details ansehen</a>
            `);

      markers.addLayer(marker);
    });
  }

  map.addLayer(markers);
});
