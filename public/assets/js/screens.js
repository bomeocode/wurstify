/**
 * EduScreen - screens.js
 * Enthält die komplette Logik für die dynamische Verwaltung von Bildschirmgruppen im Modal.
 */

// Importiert unsere universelle Toast-Funktion aus der globalen main.js
import { showToast } from "./main.js";

document.addEventListener("DOMContentLoaded", function () {
  const groupsModalEl = document.getElementById("groupsModal");
  // Wenn wir uns nicht auf der Bildschirm-Seite befinden, beende das Skript
  if (!groupsModalEl) {
    return;
  }

  const modalBody = groupsModalEl.querySelector(".modal-body");
  const modalInstance = new bootstrap.Modal(groupsModalEl);

  // --- EVENT LISTENERS ---

  // 1. Wenn das Modal geöffnet wird, lade die Daten
  groupsModalEl.addEventListener("show.bs.modal", function () {
    loadGroupEditor();
  });

  // 2. Event Delegation für alle "submit"-Aktionen (neue Gruppe, Gruppe speichern)
  modalBody.addEventListener("submit", function (event) {
    event.preventDefault();

    if (event.target.id === "newGroupForm") {
      handleNewGroupSubmit(event.target);
    } else if (event.target.classList.contains("group-update-form")) {
      handleUpdateGroupSubmit(event.target);
    }
  });

  // 3. Event Delegation für alle Klick-Aktionen (Löschen-Button)
  modalBody.addEventListener("click", function (event) {
    const deleteButton = event.target.closest(".btn-delete-group");
    if (deleteButton) {
      handleDeleteGroupClick(deleteButton);
    }
  });

  // --- DATENLADE- UND RENDER-FUNKTIONEN ---

  /**
   * Holt die Gruppen & Layouts vom Server und stößt das Rendern der UI im Modal an.
   */
  async function loadGroupEditor() {
    modalBody.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Lade...</span></div></div>';

    try {
      const response = await fetch("/screens/groups", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (!response.ok) {
        throw new Error("Netzwerkfehler beim Laden der Gruppen.");
      }

      const data = await response.json();
      renderGroupEditor(data.groups, data.layouts);
    } catch (error) {
      console.error(error);
      modalBody.innerHTML =
        '<div class="alert alert-danger">Gruppen konnten nicht geladen werden.</div>';
    }
  }

  /**
   * Baut das komplette HTML für den Gruppen-Editor in das Modal.
   */
  function renderGroupEditor(groups, layouts) {
    // HTML für das "Neue Gruppe"-Formular
    const newGroupForm = `
            <h5>Neue Gruppe erstellen</h5>
            <form id="newGroupForm">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Name der neuen Gruppe" required>
                    <button type="submit" class="btn btn-primary">Erstellen</button>
                </div>
            </form>
            <hr>
        `;

    // HTML für die Liste der existierenden Gruppen
    let groupsList = "<h5>Existierende Gruppen</h5>";
    if (groups.length > 0) {
      groupsList += '<div class="list-group">';
      groups.forEach((group) => {
        let groupLayoutOptions = '<option value="">Kein Layout</option>';
        layouts.forEach((layout) => {
          const isSelected =
            layout.uuid === group.layout_uuid ? "selected" : "";
          groupLayoutOptions += `<option value="${
            layout.uuid
          }" ${isSelected}>${escapeHTML(layout.name)}</option>`;
        });

        groupsList += `
                    <div class="list-group-item">
                        <form class="group-update-form" data-group-id="${
                          group.id
                        }">
                            <input type="hidden" name="id" value="${group.id}">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" value="${escapeHTML(
                                  group.name
                                )}" required>
                                <select name="layout_uuid" class="form-select">${groupLayoutOptions}</select>
                                <button type="submit" class="btn btn-success btn-save-group" title="Speichern"><i class="bi bi-check-lg"></i></button>
                                <button type="button" class="btn btn-danger btn-delete-group" title="Löschen"><i class="bi bi-trash"></i></button>
                            </div>
                        </form>
                    </div>`;
      });
      groupsList += "</div>";
    } else {
      groupsList += '<p class="text-muted">Noch keine Gruppen erstellt.</p>';
    }

    modalBody.innerHTML = newGroupForm + groupsList;
  }

  // --- AJAX-HANDLER-FUNKTIONEN ---

  async function handleNewGroupSubmit(form) {
    const formData = new FormData(form);
    const response = await sendForm("/screens/groups/create", formData);
    if (response.success) {
      showToast("Gruppe erfolgreich erstellt.", "success");
      loadGroupEditor(); // Lade die Liste neu, um die neue Gruppe anzuzeigen
    }
  }

  async function handleUpdateGroupSubmit(form) {
    const formData = new FormData(form);
    const response = await sendForm("/screens/groups/update", formData);
    if (response.success) {
      showToast(response.message, "success");
      // Ein Neuladen ist hier nicht zwingend nötig, aber sorgt für konsistente Daten
    }
  }

  async function handleDeleteGroupClick(button) {
    if (
      confirm(
        "Sind Sie sicher, dass Sie diese Gruppe löschen möchten? Bildschirme in dieser Gruppe werden keiner Gruppe mehr zugeordnet."
      )
    ) {
      const form = button.closest("form");
      const formData = new FormData();
      formData.append("id", form.dataset.groupId);

      const response = await sendForm("/screens/groups/delete", formData);
      if (response.success) {
        showToast(response.message, "success");
        loadGroupEditor(); // Lade die Liste neu, um die gelöschte Gruppe zu entfernen
      }
    }
  }

  /**
   * Eine zentrale Funktion zum Senden von POST-Formulardaten mit CSRF-Schutz.
   */
  async function sendForm(url, formData) {
    // Deaktiviere alle Buttons im Modal während des Sendens
    modalBody.querySelectorAll("button").forEach((b) => (b.disabled = true));

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

      // CSRF-Token für die nächste Anfrage aktualisieren (falls erneuert wird)
      const newToken = response.headers.get("X-CSRF-TOKEN");
      if (newToken)
        document.querySelector('meta[name="X-CSRF-TOKEN-VALUE"]').content =
          newToken;

      const result = await response.json();

      if (!response.ok) {
        throw new Error(
          result.error || result.message || "Ein Server-Fehler ist aufgetreten."
        );
      }
      return result;
    } catch (error) {
      showToast(error.message, "danger");
      return { success: false };
    } finally {
      // Aktiviere alle Buttons wieder, egal ob Erfolg oder Fehler
      modalBody.querySelectorAll("button").forEach((b) => (b.disabled = false));
    }
  }

  // Hilfsfunktion zum Escapen von HTML
  function escapeHTML(str) {
    if (!str) return "";
    return str
      .toString()
      .replace(
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
