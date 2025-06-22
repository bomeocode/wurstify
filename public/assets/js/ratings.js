// JavaScript für die ratings/new Seite

document.addEventListener("DOMContentLoaded", function () {
  const useLocationBtn = document.getElementById("use-current-location");
  const addressField = document.getElementById("address_manual");
  const statusText = document.getElementById("location-status");

  useLocationBtn.addEventListener("click", () => {
    if (!("geolocation" in navigator)) {
      statusText.textContent =
        "Ortungsdienste werden von Ihrem Browser nicht unterstützt.";
      statusText.className = "text-danger";
      return;
    }

    statusText.textContent = "Standort wird ermittelt...";
    statusText.className = "text-muted";

    // 1. Hole die aktuellen Koordinaten
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;

        // 2. Führe Reverse Geocoding durch, um die Adresse zu bekommen
        // Wir verwenden hier wieder die kostenlose Nominatim API
        fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
        )
          .then((response) => response.json())
          .then((data) => {
            if (data && data.display_name) {
              // Erfolg! Fülle das Adressfeld mit dem Ergebnis
              addressField.value = data.display_name;
              statusText.textContent =
                "Standort erfolgreich als Adresse eingefügt.";
              statusText.className = "text-success";
            } else {
              throw new Error(
                "Adresse konnte nicht aus Koordinaten umgewandelt werden."
              );
            }
          })
          .catch((error) => {
            console.error("Reverse Geocoding Fehler:", error);
            statusText.textContent = "Fehler beim Abrufen der Adresse.";
            statusText.className = "text-danger";
          });
      },
      (error) => {
        let message = "Ein unbekannter Fehler ist aufgetreten.";
        if (error.code === 1) message = "Zugriff auf Standort verweigert.";
        if (error.code === 2) message = "Standort nicht verfügbar.";
        if (error.code === 3) message = "Timeout bei der Standortabfrage.";

        statusText.textContent = message;
        statusText.className = "text-danger";
      }
    );
  });
});
