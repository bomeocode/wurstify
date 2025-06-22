document.addEventListener("DOMContentLoaded", function () {
  // === 1. VORBEREITUNG: Alle Elemente und Daten holen ===
  const mapContainer = document.getElementById("map-container");
  const mapGroupElement = document.getElementById("map-group");
  const svgElement = document.getElementById("deutschland-karte");
  const tooltip = document.getElementById("tooltip");
  const landPaths = document.querySelectorAll(".land");
  const iconGroup = document.getElementById("rating-icons");

  if (
    !mapContainer ||
    !mapGroupElement ||
    !svgElement ||
    !tooltip ||
    !iconGroup
  ) {
    console.error(
      "Ein oder mehrere benötigte Karten-Elemente wurden nicht im DOM gefunden."
    );
    return;
  }

  const ratingsData = JSON.parse(mapContainer.dataset.ratings || "[]");

  // === 2. HELFER-FUNKTIONEN DEFINIEREN ===

  /**
   * Skaliert alle Icons basierend auf dem aktuellen Zoom.
   * @param {Object} panzoomInstance - Die aktive Panzoom-Instanz.
   */
  function scaleIcons(panzoomInstance) {
    // Sicherheitsabfrage, falls panzoom noch nicht existiert.
    if (!panzoomInstance) return;

    const currentScale = panzoomInstance.getScale();
    const iconBaseSize = 24;
    const newSize = iconBaseSize / currentScale;

    const allIcons = iconGroup.querySelectorAll("image");
    allIcons.forEach((icon) => {
      const originalX = parseFloat(icon.dataset.originalX);
      const originalY = parseFloat(icon.dataset.originalY);
      icon.setAttribute("width", newSize);
      icon.setAttribute("height", newSize);
      icon.setAttribute("x", originalX - newSize / 2);
      icon.setAttribute("y", originalY - newSize / 2);
    });
  }

  /**
   * Zeichnet die Bewertungs-Icons auf die Karte.
   * @param {Array} ratings - Ein Array von Bewertungsobjekten.
   */
  async function drawRatingIcons(ratings) {
    if (!ratings || ratings.length === 0) {
      console.log("Keine Bewertungen mit Koordinaten zum Anzeigen vorhanden.");
      return;
    }
    try {
      const response = await fetch("/data/germany.geo.json");
      if (!response.ok)
        throw new Error(`GeoJSON ladefehler: ${response.statusText}`);
      const geoJson = await response.json();

      const { width, height } = svgElement.viewBox.baseVal;
      const projection = d3.geoMercator().fitSize([width, height], geoJson);

      iconGroup.innerHTML = "";

      ratings.forEach((rating) => {
        const [x, y] = projection([rating.longitude, rating.latitude]);
        if (!x || !y) return;

        const image = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "image"
        );
        image.dataset.originalX = x;
        image.dataset.originalY = y;
        image.setAttribute("href", "assets/img/bratwurst-icon.svg");
        image.style.cursor = "pointer";

        const title = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "title"
        );
        title.textContent = rating.vendor_name;
        image.appendChild(title);
        iconGroup.appendChild(image);
      });
    } catch (error) {
      console.error("Fehler beim Zeichnen der Icons:", error);
    }
  }

  // === 3. INITIALISIERUNG & EVENT LISTENER ===

  // Panzoom wird hier erstellt und ist ab jetzt verfügbar.
  const panzoom = Panzoom(mapGroupElement, {
    maxScale: 10,
    minScale: 0.7,
    canvas: true,
    disablePan: true,
    step: 0.5,
    duration: 100,
  });

  // Event-Listener für Tooltips
  landPaths.forEach((land) => {
    land.addEventListener("mousemove", (e) => {
      tooltip.innerHTML = land.getAttribute("data-name");
      tooltip.style.display = "block";
      const rect = mapContainer.getBoundingClientRect();
      tooltip.style.left = `${e.clientX - rect.left + 15}px`;
      tooltip.style.top = `${e.clientY - rect.top + 15}px`;
    });
    land.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
    });
  });

  // Event-Listener für das Mausrad
  svgElement.parentElement.addEventListener("wheel", panzoom.zoomWithWheel);

  // Event-Listener für das Zoomen, der die Skalierung der Icons aufruft
  mapGroupElement.addEventListener("panzoomzoom", () => {
    const isZoomed = panzoom.getScale() !== panzoom.getOptions().startScale;
    panzoom.setOptions({ disablePan: !isZoomed });

    // Ruft die Skalierungsfunktion auf und übergibt die panzoom-Instanz
    scaleIcons(panzoom);
  });

  // === 4. STARTPUNKT ===

  // Zeichne die Icons initial. Warten, bis es fertig ist, DANN initial skalieren.
  drawRatingIcons(ratingsData).then(() => {
    // Ruft die Skalierungsfunktion auf und übergibt die panzoom-Instanz
    scaleIcons(panzoom);
  });
});
