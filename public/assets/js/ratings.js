document.addEventListener("DOMContentLoaded", function () {
  const locationSection = document.getElementById("location-section");
  const manualAddressSection = document.getElementById(
    "manual-address-section"
  );
  const skipLocationBtn = document.getElementById("skip-location");

  const latField = document.getElementById("latitude");
  const lonField = document.getElementById("longitude");
  const placesDatalist = document.getElementById("nearby-places");

  // Funktion, um zur manuellen Eingabe zu wechseln
  function showManualAddress() {
    locationSection.style.display = "none";
    manualAddressSection.style.display = "block";
  }

  skipLocationBtn.addEventListener("click", showManualAddress);

  // 1. Versuche, die Geo-Location abzufragen
  if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(
      // SUCCESS-Callback
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        // Versteckte Felder befüllen
        latField.value = lat;
        lonField.value = lon;

        locationSection.innerHTML = `<p class="text-success">Standort erfasst: Lat: ${lat.toFixed(
          4
        )}, Lon: ${lon.toFixed(4)}</p>`;

        // 2. ORTE IN DER NÄHE VORSCHLAGEN (API-Aufruf)
        fetchNearbyPlaces(lat, lon);
      },
      // ERROR-Callback
      (error) => {
        console.error("Geolocation Error:", error.message);
        locationSection.innerHTML =
          '<p class="text-danger">Standort konnte nicht ermittelt werden. Bitte geben Sie eine Adresse ein.</p>';
        showManualAddress();
      }
    );
  } else {
    // Geolocation wird nicht unterstützt
    console.log("Geolocation wird von diesem Browser nicht unterstützt.");
    showManualAddress();
  }

  /**
   * Fragt Orte in der Nähe über die Nominatim API (OpenStreetMap) ab.
   * WICHTIG: Beachten Sie die Nutzungsbedingungen von Nominatim! (Nicht mehr als 1 Anfrage/Sekunde)
   * Für kommerzielle Projekte eine andere API wie Google Places in Betracht ziehen.
   */
  function fetchNearbyPlaces(lat, lon) {
    // Wir suchen nach Restaurants, Imbissen etc. im Umkreis von ca. 500m
    const radius = 0.005;
    const query = `[out:json];(node["amenity"~"restaurant|fast_food|food_court|cafe|biergarten"](around:500,${lat},${lon});way["amenity"~"restaurant|fast_food|food_court|cafe|biergarten"](around:500,${lat},${lon}););out;`;

    fetch(
      `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(
        query
      )}`
    )
      .then((response) => response.json())
      .then((data) => {
        placesDatalist.innerHTML = ""; // Alte Vorschläge löschen
        const places = new Set(); // Um Duplikate zu vermeiden

        data.elements.forEach((element) => {
          if (element.tags && element.tags.name) {
            places.add(element.tags.name);
          }
        });

        places.forEach((name) => {
          const option = document.createElement("option");
          option.value = name;
          placesDatalist.appendChild(option);
        });
      })
      .catch((error) => console.error("Fehler beim Abrufen der Orte:", error));
  }
});
