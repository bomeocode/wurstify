import { showToast } from "./main.js";

document.addEventListener("DOMContentLoaded", function () {
  const editorContainer = document.querySelector(".layout-editor-container");
  const assignmentModalEl = document.getElementById("assignmentModal");

  if (!editorContainer || !assignmentModalEl) {
    return;
  }

  const assignmentModal = new bootstrap.Modal(assignmentModalEl);
  const modalBody = assignmentModalEl.querySelector(".modal-body");
  const modalTitle = assignmentModalEl.querySelector(".modal-title");

  let currentEditingSlot = null; // Speichert den Namen des Slots

  // Listener für "Inhalt zuweisen"-Buttons
  editorContainer.addEventListener("click", function (event) {
    const assignButton = event.target.closest(".js-assign-content");
    if (assignButton) {
      currentEditingSlot = assignButton.dataset.slotName;
      modalTitle.textContent = `Inhalt für Slot "${currentEditingSlot}" auswählen`;
      loadMediaLibrary();
      assignmentModal.show();
    }
  });

  // NEU: Listener für Klicks auf Medien INNERHALB des Modals
  modalBody.addEventListener("click", function (event) {
    const clickedItem = event.target.closest(".js-media-item");

    if (clickedItem && currentEditingSlot) {
      const mediaUuid = clickedItem.dataset.mediaUuid;
      const layoutUuid = editorContainer.dataset.layoutUuid;

      assignmentModal.hide(); // Modal sofort schließen
      saveSlotAssignment(layoutUuid, currentEditingSlot, mediaUuid);
    }
  });

  /**
   * Sendet die Zuweisung an den Server und aktualisiert die UI.
   */
  async function saveSlotAssignment(layoutUuid, slotName, mediaUuid) {
    const url = `/layouts/update_slot/${layoutUuid}/${slotName}`;
    const formData = new FormData();
    formData.append("media_uuid", mediaUuid);

    const csrfHeader = document.querySelector(
      'meta[name="X-CSRF-TOKEN-NAME"]'
    ).content;
    const csrfToken = document.querySelector(
      'meta[name="X-CSRF-TOKEN-VALUE"]'
    ).content;

    try {
      const response = await fetch(url, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          [csrfHeader]: csrfToken,
        },
        body: formData,
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(
          result.message || "Ein unbekannter Fehler ist aufgetreten."
        );
      }

      // Hier kannst du deine universelle Toast-Funktion aufrufen
      showToast(result.message, "success");

      // Wichtig: CSRF-Token für die nächste Anfrage aktualisieren (falls erneuert)
      const newToken = response.headers.get("X-CSRF-TOKEN");
      if (newToken) {
        document.querySelector('meta[name="X-CSRF-TOKEN-VALUE"]').content =
          newToken;
      }

      // Aktualisiere die Editor-Ansicht mit den neuen Daten
      updateSlotUI(result.slot);
    } catch (error) {
      console.error("Fehler beim Speichern des Slots:", error);
      showToast("Fehler: " + error.message, "danger");
    }
  }

  /**
   * Aktualisiert die Anzeige eines einzelnen Slots im Editor,
   * indem die korrekte Vorschau (Bild oder Icon) angezeigt wird.
   */
  function updateSlotUI(slot) {
    const slotButton = document.querySelector(
      `.js-assign-content[data-slot-name="${slot.name}"]`
    );
    if (!slotButton) return;

    const slotContainer = slotButton.closest(".layout-slot");
    if (!slotContainer) return;

    // Schritt 1: Entscheide, welches Thumbnail/Icon angezeigt werden soll
    let thumbnailHTML = "";
    if (slot.file_type.startsWith("image/")) {
      thumbnailHTML = `<img src="${slot.preview_url}" alt="${escapeHTML(
        slot.original_name
      )}" class="img-fluid">`;
    } else if (slot.file_type === "application/pdf") {
      thumbnailHTML = `<i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 2.5rem;"></i>`;
    } else {
      thumbnailHTML = `<i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>`;
    }

    // Schritt 2: Baue das komplette neue HTML für den Slot zusammen
    const newContent = `
            <div class="text-center p-2">
                <div class="media-thumbnail mx-auto mb-2">
                    ${thumbnailHTML}
                </div>
                <p class="mb-1 small text-muted" style="word-break: break-all; line-height: 1.2;">
                    ${escapeHTML(slot.original_name)}
                </p>
                <button class="btn btn-sm btn-secondary js-assign-content mt-1" data-slot-name="${
                  slot.name
                }">Ändern</button>
            </div>`;

    // Schritt 3: Ersetze den alten Inhalt des Slots durch den neuen
    slotContainer.innerHTML = newContent;
  }

  // Die Funktion loadMediaLibrary und escapeHTML bleiben unverändert
  /**
   * Lädt die HTML-Ansicht der Medienbibliothek per AJAX
   * und fügt sie in den Modal-Body ein.
   */
  /**
   * Lädt die HTML-Ansicht der Medienbibliothek per AJAX
   * und fügt sie in den Modal-Body ein.
   * DIESE VERSION ERWARTET EINE JSON-ANTWORT.
   */
  async function loadMediaLibrary() {
    modalBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Lade...</span></div></div>';

    try {
      const csrfHeader = document.querySelector(
        'meta[name="X-CSRF-TOKEN-NAME"]'
      ).content;
      const csrfToken = document.querySelector(
        'meta[name="X-CSRF-TOKEN-VALUE"]'
      ).content;

      const response = await fetch("/media?context=modal", {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          [csrfHeader]: csrfToken,
        },
      });

      if (!response.ok) {
        throw new Error(
          `Netzwerk-Antwort war nicht ok. Status: ${response.status}`
        );
      }

      // NEU: Verarbeite die Antwort als JSON
      const data = await response.json();
      // NEU: Extrahiere das HTML aus dem JSON-Objekt
      const html = data.html;

      // Prüfe, ob das extrahierte HTML echten Inhalt hat
      if (html && html.includes("js-media-item")) {
        // Leere den Modal-Body (entfernt den Spinner)
        modalBody.innerHTML = "";

        // Füge die Anleitung hinzu
        modalBody.insertAdjacentHTML(
          "afterbegin",
          '<p class="text-muted">Klicken Sie auf ein Medium, um es diesem Slot zuzuweisen.</p>'
        );

        // Füge den HTML-Inhalt direkt ein
        modalBody.insertAdjacentHTML("beforeend", html);
      } else {
        // Falls keine Medien gefunden wurden
        modalBody.innerHTML = "<p>Keine Medien in der Bibliothek gefunden.</p>";
      }
    } catch (error) {
      console.error("Fehler beim Laden der Medien-Bibliothek:", error);
      modalBody.innerHTML =
        '<div class="alert alert-danger">Die Medien-Bibliothek konnte nicht geladen werden.</div>';
    }
  }

  function escapeHTML(str) {
    return str.replace(
      /[&<>"']/g,
      (match) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[match])
    );
  }
});
