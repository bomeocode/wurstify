// JavaScript für die ratings/new Seite

document.addEventListener("DOMContentLoaded", function () {
  const useLocationBtn = document.getElementById("use-current-location");
  const addressField = document.getElementById("address_manual");
  const vendorNameField = document.getElementById("vendor_name");
  const suggestionsContainer = document.getElementById("vendor-suggestions");
  const statusText = document.getElementById("location-status");

  // --- Logik für den Ortungs-Button ---
  useLocationBtn.addEventListener("click", () => {
    if (!("geolocation" in navigator)) {
      statusText.textContent =
        "Ortungsdienste werden von Ihrem Browser nicht unterstützt.";
      return;
    }

    statusText.textContent = "Standort wird ermittelt...";
    statusText.className = "text-muted";

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;

        statusText.textContent = "Suche Anbieter und Adresse...";

        // AKTION 1: Suche nach bekannten Anbietern in der Nähe (wie bisher)
        fetchVendors(`lat=${latitude}&lon=${longitude}`);

        // NEU - AKTION 2: Hole die Adresse zu den Koordinaten und fülle das Feld
        fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
        )
          .then((response) => response.json())
          .then((data) => {
            if (data && data.display_name) {
              // Erfolg! Fülle das Adressfeld mit dem Ergebnis
              addressField.value = data.display_name;
            }
          })
          .catch((error) => {
            console.error("Fehler beim Reverse Geocoding:", error);
            // Dies ist nicht kritisch, die Anbietersuche kann trotzdem funktionieren.
          });
      },
      (error) => {
        // ... (Ihre bestehende Fehlerbehandlung) ...
        let message = "Ein unbekannter Fehler ist aufgetreten.";
        if (error.code === 1) message = "Zugriff auf Standort verweigert.";
        statusText.textContent = message;
        statusText.className = "text-danger";
      }
    );
  });

  // --- Logik für die manuelle Adresseingabe ---
  let debounceTimer;
  addressField.addEventListener("keyup", () => {
    clearTimeout(debounceTimer);
    const query = addressField.value;
    if (query.length < 3) {
      suggestionsContainer.innerHTML = ""; // Vorschläge bei kurzer Eingabe leeren
      return;
    }
    // Debounce: Warte 500ms nach der letzten Eingabe, bevor die Suche startet
    debounceTimer = setTimeout(() => {
      statusText.textContent = "Suche nach passenden Anbietern...";
      fetchVendors(`q=${encodeURIComponent(query)}`);
    }, 500);
  });

  // --- Funktion, um Anbieter vom Backend zu holen ---
  async function fetchVendors(params) {
    try {
      const response = await fetch(`/api/vendor-search?${params}`);
      const vendors = await response.json();
      renderSuggestions(vendors);
    } catch (error) {
      console.error("Fehler beim Holen der Anbieter:", error);
      statusText.textContent = "Suche fehlgeschlagen.";
    }
  }

  // --- Funktion, um die Vorschläge anzuzeigen ---
  function renderSuggestions(vendors) {
    suggestionsContainer.innerHTML = ""; // Alte Vorschläge leeren
    if (vendors.length === 0) {
      statusText.textContent =
        "Keine bekannten Anbieter für diesen Ort gefunden.";
      return;
    }

    statusText.textContent = `${vendors.length} Anbieter gefunden:`;
    vendors.forEach((vendor) => {
      const link = document.createElement("a");
      link.href = "#";
      link.className = "list-group-item list-group-item-action";
      link.textContent = `${vendor.name} (${
        vendor.address || "Adresse nicht bekannt"
      })`;

      link.addEventListener("click", (e) => {
        e.preventDefault();
        // Formularfelder bei Auswahl eines Vorschlags befüllen
        vendorNameField.value = vendor.name;
        addressField.value = vendor.address || "";
        suggestionsContainer.innerHTML = ""; // Vorschläge ausblenden
        statusText.textContent = `Anbieter "${vendor.name}" ausgewählt.`;
      });
      suggestionsContainer.appendChild(link);
    });
  }
});
