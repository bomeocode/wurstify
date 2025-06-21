// Importiert die pdf.js Bibliothek
import * as pdfjsLib from "/assets/pdf/pdf.js";

// Globale Kontroll-Variable, um Anfragen zu verfolgen.
let currentPreviewId = 0;

// Pfad zum PDF.js Worker setzen.
pdfjsLib.GlobalWorkerOptions.workerSrc = "/assets/pdf/pdf.worker.js";

// Haupt-Event-Listener wird erst ausgeführt, wenn das DOM geladen ist.
document.addEventListener("DOMContentLoaded", function () {
  const previewModalEl = document.getElementById("previewModal");
  if (!previewModalEl) return;

  const modalTitle = previewModalEl.querySelector(".modal-title");
  const modalBody = previewModalEl.querySelector(".modal-body");

  // Event-Listener, der feuert, bevor das Modal angezeigt wird
  previewModalEl.addEventListener("show.bs.modal", (event) => {
    // Erhöhe die ID für diese neue Anfrage
    currentPreviewId++;
    const thisRequestId = currentPreviewId;

    const button = event.relatedTarget;
    const uuid = button.dataset.uuid;
    const mimeType = button.dataset.mimeType;
    const serveUrl = `/media/serve/${uuid}`;

    // Initialen Lade-Spinner setzen
    modalBody.innerHTML =
      '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';

    if (mimeType.startsWith("image/")) {
      modalTitle.textContent = "Bild-Vorschau";
      // Rufe die neue, sichere Funktion zum Rendern von Bildern auf
      renderImage(modalBody, serveUrl, thisRequestId);
    } else if (mimeType === "application/pdf") {
      modalTitle.textContent = "PDF-Vorschau (Seite 1)";
      // Rufe die sichere Funktion zum Rendern von PDFs auf
      renderPdfPage(modalBody, serveUrl, 1, thisRequestId);
    } else {
      modalTitle.textContent = "Vorschau nicht verfügbar";
      modalBody.innerHTML = `<p>Für diesen Dateityp (${mimeType}) ist keine Vorschau verfügbar.</p><a href="${serveUrl}" class="btn btn-primary" download>Datei herunterladen</a>`;
    }
  });

  // Leert das Modal, nachdem es geschlossen wurde
  previewModalEl.addEventListener("hidden.bs.modal", () => {
    modalBody.innerHTML = "";
    modalTitle.textContent = "Vorschau";
    // Setze die ID zurück, um zu signalisieren, dass keine Anfrage mehr aktiv ist.
    currentPreviewId = 0;
  });

  /**
   * NEUE FUNKTION: Rendert ein Bild sicher in einen Container.
   * @param {HTMLElement} container - Das Elternelement (modal-body).
   * @param {string} url - Die URL zum Bild.
   * @param {number} requestId - Die ID dieser spezifischen Anfrage.
   */
  function renderImage(container, url, requestId) {
    const img = document.createElement("img");
    img.src = url;
    img.className = "img-fluid";

    // Der Code hier wird erst ausgeführt, wenn das Bild fertig geladen ist.
    img.onload = () => {
      // Prüfen, ob dies noch die aktuellste Anfrage ist.
      if (requestId !== currentPreviewId) {
        console.log(`Breche veraltete Bild-Anfrage ab (ID: ${requestId})`);
        return; // Arbeit abbrechen!
      }
      // Wenn alles passt, Spinner entfernen und Bild anzeigen.
      container.innerHTML = "";
      container.appendChild(img);
    };

    // Fehlerbehandlung, falls das Bild nicht geladen werden kann
    img.onerror = () => {
      if (requestId === currentPreviewId) {
        container.innerHTML =
          '<p class="text-danger">Das Bild konnte nicht geladen werden.</p>';
      }
    };
  }

  /**
   * Rendert eine einzelne Seite eines PDFs auf ein Canvas-Element.
   * DIESE VERSION ZEIGT EINEN LADE-SPINNER WÄHREND DER PDF-VERARBEITUNG AN.
   * @param {HTMLElement} container - Das Elternelement (modal-body).
   * @param {string} url - Die URL zur PDF-Datei.
   * @param {number} pageNum - Die Seitenzahl.
   * @param {number} requestId - Die ID dieser spezifischen Anfrage.
   */
  async function renderPdfPage(container, url, pageNum, requestId) {
    // Erstelle das Canvas-Element nur im Speicher, füge es noch NICHT zum DOM hinzu.
    // Der Spinner vom Modal ist also weiterhin sichtbar.
    const canvas = document.createElement("canvas");

    try {
      // Lade das PDF-Dokument. Dies ist der langsame Teil.
      const pdf = await pdfjsLib.getDocument(url).promise;

      // Überprüfe, ob die Anfrage noch aktuell ist
      if (requestId !== currentPreviewId) {
        return;
      }

      const page = await pdf.getPage(pageNum);

      if (requestId !== currentPreviewId) {
        return;
      }

      // Berechne die korrekte, responsive Größe
      const unscaledViewport = page.getViewport({ scale: 1 });
      const availableWidth = container.clientWidth;
      const scale = availableWidth / unscaledViewport.width;
      const viewport = page.getViewport({ scale: scale });

      canvas.height = viewport.height;
      canvas.width = viewport.width;
      const context = canvas.getContext("2d");

      // Rendere die PDF-Seite auf unser unsichtbares Canvas. Dies kann auch einen Moment dauern.
      await page.render({
        canvasContext: context,
        viewport: viewport,
      }).promise;

      // ---- ERFOLG ----
      // Erst JETZT, nachdem alles fertig ist, tauschen wir den Inhalt aus.
      if (requestId === currentPreviewId) {
        container.innerHTML = ""; // Entferne den Spinner
        container.appendChild(canvas); // Füge das fertige Canvas-Bild hinzu
      }
    } catch (error) {
      console.error("Fehler beim Rendern des PDFs:", error);
      // Zeige eine Fehlermeldung, falls etwas schiefgeht
      if (requestId === currentPreviewId) {
        container.innerHTML =
          '<p class="text-danger">PDF-Vorschau konnte nicht geladen werden.</p>';
      }
    }
  }

  // Dieser Code kommt innerhalb deines DOMContentLoaded-Listeners,
  // aber außerhalb anderer Funktionen.
  const uploadForm = document.getElementById("uploadForm");

  if (uploadForm) {
    const uploadButton = document.getElementById("uploadButton");
    const uploadProgress = document.getElementById("uploadProgress");
    const progressBar = document.getElementById("progressBar");
    const uploadStatus = document.getElementById("uploadStatus");

    uploadForm.addEventListener("submit", function (e) {
      e.preventDefault(); // Standard-Formular-Upload verhindern!

      const fileInput = document.getElementById("userfile");
      if (fileInput.files.length === 0) {
        uploadStatus.innerHTML =
          '<div class="alert alert-danger">Bitte wählen Sie zuerst eine Datei aus.</div>';
        return;
      }

      const formData = new FormData(this);
      const xhr = new XMLHttpRequest();

      // WIEDER AKTIVIERT: Der Listener für den echten Fortschritt
      xhr.upload.addEventListener("progress", function (e) {
        if (e.lengthComputable) {
          const percentComplete = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = percentComplete + "%";
          progressBar.setAttribute("aria-valuenow", percentComplete);
          progressBar.textContent = percentComplete + "%";
        }
      });

      // Listener für den Upload-Fortschritt
      xhr.addEventListener("load", function () {
        uploadButton.disabled = false;
        // Die Animation wird jetzt hier gestoppt
        progressBar.classList.remove("progress-bar-animated");

        let response;
        try {
          response = JSON.parse(xhr.responseText);
        } catch (e) {
          response = {
            message: `Ein Server-Fehler ist aufgetreten (Status: ${xhr.status}).`,
            type: "danger",
          };
        }

        if (xhr.status === 200 && response.success) {
          // Erfolgsfall
          progressBar.classList.add("bg-success");
          progressBar.textContent = "Erfolgreich!";
          showToast(response.message, response.type);
          addNewMediaItemToList(response.medium);
          uploadForm.reset();
        } else {
          // Fehlerfall
          progressBar.classList.add("bg-danger");
          progressBar.textContent = "Fehler!";
          showToast(
            response.message || "Ein unbekannter Fehler ist aufgetreten.",
            "danger"
          );
        }

        // Blende den Balken nach kurzer Zeit wieder aus
        setTimeout(() => {
          uploadProgress.classList.add("d-none");
        }, 2000);
      });

      // Listener für Fehler
      xhr.addEventListener("error", function () {
        uploadStatus.innerHTML =
          '<div class="alert alert-danger">Ein Netzwerkfehler ist aufgetreten.</div>';
        uploadButton.disabled = false;
      });

      // Upload starten
      xhr.open("POST", this.action, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

      // Der CSRF-Header wird weiterhin gesendet, auch wenn er für diese Route nicht mehr geprüft wird
      const csrfHeader = document.querySelector(
        'meta[name="X-CSRF-TOKEN-NAME"]'
      ).content;
      const csrfToken = document.querySelector(
        'meta[name="X-CSRF-TOKEN-VALUE"]'
      ).content;
      xhr.setRequestHeader(csrfHeader, csrfToken);

      // UI vorbereiten (setzt den Balken auf 0% zurück)
      uploadButton.disabled = true;
      uploadStatus.innerHTML = "";
      uploadProgress.classList.remove("d-none");
      progressBar.style.width = "0%"; // Wichtig: Startet bei 0%
      progressBar.textContent = "0%";
      progressBar.classList.remove("bg-success", "bg-danger");
      progressBar.classList.add("progress-bar-animated");
      progressBar.classList.add("bg-info");
      xhr.send(formData);
    });
  }

  /**
   * Fügt ein neues Medienelement dynamisch an den Anfang der Liste an.
   * DIESE FINALE VERSION ERZEUGT DAS KOMPLETTE NEUE LAYOUT INKL. THUMBNAILS.
   * @param {object} medium - Das Medium-Objekt vom Server
   */
  function addNewMediaItemToList(medium) {
    const listGroup = document.querySelector(".list-group");
    if (!listGroup) return;

    // Schritt 1: Entscheide, welches Thumbnail/Icon angezeigt werden soll
    let thumbnailHTML = "";
    const serveUrl = `/media/serve/${medium.uuid}`;

    if (medium.file_type.startsWith("image/")) {
      thumbnailHTML = `<img src="${serveUrl}" alt="${escapeHTML(
        medium.original_name
      )}" class="img-fluid">`;
    } else if (medium.file_type === "application/pdf") {
      thumbnailHTML = `<i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 2.5rem;"></i>`;
    } else {
      thumbnailHTML = `<i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>`;
    }

    // Schritt 2: Baue das komplette HTML für das neue Listenelement zusammen
    const confirmMessage = `Sind Sie sicher, dass Sie die Datei ${JSON.stringify(
      medium.original_name
    )} löschen möchten?`;

    const newItemHTML = `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="media-thumbnail me-3">
                    ${thumbnailHTML}
                </div>
                <div>
                    <strong class="media-filename" data-bs-toggle="tooltip" title="${escapeHTML(
                      medium.original_name
                    )}">
                        ${escapeHTML(medium.original_name)}
                    </strong>
                    <small class="text-muted d-block">
                        Größe: ${medium.readable_size} | Typ: ${
      medium.file_type
    }
                    </small>
                </div>
            </div>
            <div class="btn-group" role="group" aria-label="Aktionen">
                <button type="button" class="btn btn-outline-secondary btn-sm js-preview-media" 
                        data-bs-toggle="modal" 
                        data-bs-target="#previewModal"
                        data-uuid="${medium.uuid}"
                        data-mime-type="${medium.file_type}"
                        title="Vorschau anzeigen">
                    <i class="bi-eye-fill"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm js-delete-media"
                        data-delete-url="/media/delete/${medium.uuid}"
                        data-confirm-message="${escapeHTML(confirmMessage)}"
                        title="Medium löschen">
                    <i class="bi-trash-fill"></i>
                </button>
            </div>
        </div>`;

    // Schritt 3: Füge das neue Element am Anfang der Liste ein
    listGroup.insertAdjacentHTML("afterbegin", newItemHTML);

    // Schritt 4: GANZ WICHTIG - Initialisiere die Tooltips für das neue Element!
    initializeTooltips();
  }

  // Die Funktion zum Initialisieren der Tooltips (sollte bereits in deiner main.js sein)
  function initializeTooltips() {
    // Entferne zuerst alte, "tote" Tooltips
    const oldTooltips = document.querySelectorAll(".tooltip");
    oldTooltips.forEach((t) => t.remove());

    // Initialisiere alle neuen Tooltips
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Kleine Hilfsfunktion, um HTML zu escapen
  function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (match) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[match];
    });
  }

  /**
   * Erzeugt und zeigt einen Bootstrap-Toast dynamisch an.
   * @param {string} message - Die anzuzeigende Nachricht.
   * @param {string} type - Der Typ des Toasts ('success', 'danger', 'warning', 'info').
   */
  function showToast(message, type = "info") {
    const container = document.querySelector(".toast-container");
    if (!container) return;

    // Map für Typen zu Bootstrap-Klassen
    const toastClasses = {
      success: "bg-success text-white",
      danger: "bg-danger text-white",
      warning: "bg-warning text-dark",
      info: "bg-info text-dark",
    };

    const toastId = "toast-" + Date.now();
    const toastHTML = `
            <div id="${toastId}" class="toast ${
      toastClasses[type] || toastClasses["info"]
    }" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header ${
                  toastClasses[type] || toastClasses["info"]
                } border-0">
                    <strong class="me-auto">System-Meldung</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>`;

    // Füge den neuen Toast zum Container hinzu
    container.insertAdjacentHTML("beforeend", toastHTML);

    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });

    // Entferne das Toast-Element aus dem DOM, nachdem es ausgeblendet wurde
    toastEl.addEventListener("hidden.bs.toast", () => {
      toastEl.remove();
    });

    toast.show();
  }

  // ########## LOGIK FÜR LAZY LOADING, SUCHE & FILTER ##########

  // --- DOM-Elemente holen ---
  const mediaList = document.getElementById("media-list");
  const loadMoreTrigger = document.getElementById("load-more-trigger");
  const searchInput = document.getElementById("media-search");
  const filterSelect = document.getElementById("media-filter");

  // Stelle sicher, dass wir auf der richtigen Seite sind
  if (!mediaList || !loadMoreTrigger) {
    return;
  }

  // --- Zustandsvariablen ---
  let currentPage = 1;
  let isLoading = false;
  let noMoreData = false;

  // --- Debounce-Funktion ---
  function debounce(func, delay = 350) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        func.apply(this, args);
      }, delay);
    };
  }

  // --- ZENTRALE FUNKTION ZUM LADEN VON MEDIEN ---

  async function fetchMedia(page = 1, append = false) {
    if (isLoading || (noMoreData && append)) {
      return;
    }
    isLoading = true;
    loadMoreTrigger.style.display = "block";

    const searchTerm = searchInput.value;
    const fileType = filterSelect.value;

    // Dieser Teil zum Bauen der URL ist jetzt korrekt
    let url = `/media?page=${page}`;
    if (searchTerm) {
      url += `&q=${encodeURIComponent(searchTerm)}`;
    }
    if (fileType) {
      url += `&type=${encodeURIComponent(fileType)}`;
    }

    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (response.ok) {
        // Die Antwort wird als JSON erwartet
        const data = await response.json();
        const html = data.html;

        // DIE FINALE LÖSUNG:
        // Prüfe, ob die Antwort einen echten Listeneintrag enthält,
        // anstatt nur auf die Länge zu prüfen.
        if (html.includes("list-group-item")) {
          if (append) {
            mediaList.insertAdjacentHTML("beforeend", html);
          } else {
            mediaList.innerHTML = html;
          }
          noMoreData = false;
          observer.observe(loadMoreTrigger);
        } else {
          // Wenn kein 'list-group-item' gefunden wurde, sind wir definitiv am Ende.
          noMoreData = true;
          loadMoreTrigger.style.display = "none";
          observer.unobserve(loadMoreTrigger);
        }
      } else {
        loadMoreTrigger.style.display = "none";
      }
    } catch (error) {
      console.error("Fehler beim Laden der Medien:", error);
      loadMoreTrigger.style.display = "none";
    } finally {
      isLoading = false;
    }
  }

  // --- EVENT LISTENER ---
  if (searchInput) {
    searchInput.addEventListener(
      "input",
      debounce(() => {
        currentPage = 1;
        fetchMedia(currentPage, false);
      })
    );
  }

  if (filterSelect) {
    filterSelect.addEventListener("change", () => {
      currentPage = 1;
      fetchMedia(currentPage, false);
    });
  }

  // --- INTERSECTION OBSERVER ---
  const observer = new IntersectionObserver(
    (entries) => {
      // Prüfe nur, ob der Trigger sichtbar wird und wir nicht schon laden
      if (entries[0].isIntersecting && !isLoading) {
        // NEU: Pausiere die Beobachtung sofort, um Doppel-Trigger zu verhindern
        observer.unobserve(loadMoreTrigger);

        currentPage++;
        fetchMedia(currentPage, true);
      }
    },
    {
      rootMargin: "0px 0px 300px 0px",
    }
  );

  // Starte die initiale Beobachtung
  observer.observe(loadMoreTrigger);

  // --- NEU: Zentraler Event-Listener für die Medienliste ---
  if (mediaList) {
    mediaList.addEventListener("click", function (event) {
      // Prüfen, ob auf einen Löschen-Button geklickt wurde
      const deleteButton = event.target.closest(".js-delete-media");
      if (deleteButton) {
        const url = deleteButton.dataset.deleteUrl;
        const message = deleteButton.dataset.confirmMessage;

        if (confirm(message)) {
          window.location.href = url; // Leite zur Löschen-URL weiter
        }
        return; // Beende die Funktion hier
      }

      // Die Logik für den Vorschau-Button ist bereits an das Modal selbst gebunden
      // und sollte dank Bootstrap's Event Delegation von Haus aus funktionieren.
      // Ein Klick auf .js-preview-media löst das 'show.bs.modal' Event aus,
      // auf das unser anderer Listener bereits wartet.
    });
  }
});
