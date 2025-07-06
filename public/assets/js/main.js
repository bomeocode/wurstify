/**
 * Erzeugt und zeigt einen Bootstrap-Toast dynamisch an.
 * Diese Funktion kann von anderen JS-Modulen importiert werden.
 * @param {string} message - Die anzuzeigende Nachricht.
 * @param {string} type - Der Typ des Toasts ('success', 'danger', 'warning', 'info').
 */
export function showToast(message, type = "info") {
  const container = document.querySelector(".toast-container");
  if (!container) {
    console.error("Toast container not found!");
    // Fallback auf alert, wenn der Container fehlt
    alert(message);
    return;
  }

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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>`;

  container.insertAdjacentHTML("beforeend", toastHTML);

  const toastEl = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastEl, { delay: 3000 });

  toastEl.addEventListener("hidden.bs.toast", () => {
    toastEl.remove();
  });

  toast.show();
}

document.addEventListener("click", function (e) {
  const feedbackLink = e.target.closest(".nav-feedback");

  if (feedbackLink) {
    // 1. Haptisches Feedback (funktioniert nur auf unterstützten Mobilgeräten)
    if (navigator.vibrate) {
      navigator.vibrate(50); // Eine sehr kurze Vibration von 50ms
    }

    // 2. Visuelles Feedback: Klasse für den Klick-Effekt hinzufügen
    feedbackLink.classList.add("is-active-feedback");

    // Die Klasse nach einer kurzen Verzögerung wieder entfernen,
    // falls die Navigation aus irgendeinem Grund nicht stattfindet.
    setTimeout(() => {
      feedbackLink.classList.remove("is-active-feedback");
    }, 400);

    // Wichtig: Wir rufen hier NICHT e.preventDefault() auf,
    // da der Link ja seine normale Funktion (Navigation oder Modal öffnen) ausführen soll.
  }
});

// Funktion zum Prüfen auf neue Bewertungen
async function checkForNewRatings() {
  const feedBadge = document.getElementById("feed-badge");
  const userIdMeta = document.querySelector('meta[name="user-id"]');

  // Wir führen die Funktion nur aus, wenn ein Nutzer eingeloggt ist
  if (!feedBadge || !userIdMeta) return;

  const userId = userIdMeta.content;
  const storageKey = `wurstify_feed_last_visit_${userId}`; // Benutzerspezifischer Schlüssel
  const lastVisit = localStorage.getItem(storageKey);

  // Wir fragen nur an, wenn der Nutzer den Feed schon mal besucht hat.
  if (lastVisit) {
    try {
      const response = await fetch(`/api/feed/new-count?since=${lastVisit}`);
      const data = await response.json();

      if (data.new_count > 0) {
        feedBadge.textContent = data.new_count > 9 ? "9+" : data.new_count;
        feedBadge.style.display = "inline";
      } else {
        feedBadge.style.display = "none";
      }
    } catch (e) {
      console.error("Fehler beim Prüfen auf neue Bewertungen:", e);
    }
  }
}

// Führe die Prüfung beim Laden jeder Seite aus
checkForNewRatings();

window.loadContentIntoModal = async function (url, title) {
  const modalElement = document.getElementById("ajax-modal");
  if (!modalElement) return;

  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  modalTitle.textContent = "Lade...";
  modalBody.innerHTML =
    '<div class="text-center p-5"><div class="spinner-border"></div></div>';
  if (!bsModal._isShown) bsModal.show();

  try {
    const response = await fetch(url, {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    if (!response.ok) throw new Error("Inhalt konnte nicht geladen werden.");
    modalBody.innerHTML = await response.text();
    modalTitle.textContent =
      modalBody.querySelector("h2,h1")?.textContent || title;
  } catch (error) {
    modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
  }
};
