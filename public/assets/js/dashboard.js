document.addEventListener("DOMContentLoaded", () => {
  const tooltip = document.getElementById("tooltip");
  const lands = document.querySelectorAll(".land");
  const mapContainer = document.getElementById("map-container");

  lands.forEach((land) => {
    // Event-Listener für das Betreten des Bundeslandes mit der Maus
    land.addEventListener("mousemove", (e) => {
      const landName = land.getAttribute("data-name");
      tooltip.innerHTML = landName;
      tooltip.style.display = "block";

      // Positioniert den Tooltip in der Nähe des Mauszeigers
      // Die Koordinaten müssen relativ zum mapContainer sein
      const rect = mapContainer.getBoundingClientRect();
      tooltip.style.left = `${e.clientX - rect.left + 15}px`;
      tooltip.style.top = `${e.clientY - rect.top + 15}px`;
    });

    // Event-Listener für das Verlassen des Bundeslandes
    land.addEventListener("mouseleave", () => {
      tooltip.style.display = "none";
    });
  });

  // --- KORRIGIERTER Panzoom-Block ---
  const mapGroupElement = document.getElementById("map-group");
  const svgElement = document.getElementById("deutschland-karte");

  if (mapGroupElement && svgElement) {
    const panzoom = Panzoom(mapGroupElement, {
      maxScale: 10,
      minScale: 0.7,
      canvas: true,
      disablePan: true, // Panning ist am Anfang korrekt deaktiviert
      step: 0.5,
      duration: 100,
    });

    // Mausrad-Zoom für Desktop (bleibt unverändert)
    svgElement.parentElement.addEventListener("wheel", panzoom.zoomWithWheel);

    // KORREKTUR: Der Listener muss am 'mapGroupElement' hängen, nicht am 'svgElement'.
    mapGroupElement.addEventListener("panzoomzoom", (event) => {
      const isZoomed = event.detail.scale !== panzoom.getOptions().startScale;

      // Panning erlauben, sobald der Zoom-Faktor nicht mehr dem Start-Faktor entspricht
      panzoom.setOptions({
        disablePan: !isZoomed,
      });
    });
  }
});
