// public/js/app.js

// Diese Funktion macht die Toast-Funktion global verfügbar
function showToast(message, type = "info") {
  const container = document.querySelector(".toast-container");
  if (!container) {
    console.error("Toast container not found!");
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
window.showToast = showToast;

document.addEventListener("DOMContentLoaded", function () {
  const modalElement = document.getElementById("ajax-modal");
  const offcanvasElement = document.getElementById("ajax-offcanvas");

  if (!modalElement || !offcanvasElement) return;

  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  const offcanvasTitle = offcanvasElement.querySelector(".offcanvas-title");
  const offcanvasBody = offcanvasElement.querySelector(".offcanvas-body");
  const bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);

  // ZENTRALER KLICK-LISTENER, der alles steuert
  document.addEventListener("click", async function (e) {
    const modalTrigger = e.target.closest(".open-modal-form, .open-user-modal");
    const offcanvasTrigger = e.target.closest(".open-vendor-modal");

    // Fall 1: Ein Modal soll geöffnet werden
    if (modalTrigger) {
      e.preventDefault();
      const url = modalTrigger.dataset.url;
      const title = modalTrigger.title || "Information";
      await loadInOverlay(url, title, "modal");
    }

    // Fall 2: Ein Offcanvas soll geöffnet werden
    if (offcanvasTrigger) {
      e.preventDefault();
      const url = offcanvasTrigger.dataset.url;
      const title = offcanvasTrigger.title || "Details";
      await loadInOverlay(url, title, "offcanvas", (container) => {
        // Wenn die Vendor-Details geladen sind, starten wir das Lazy-Loading
        if (window.initializeLazyLoading) {
          window.initializeLazyLoading(
            container,
            offcanvasTrigger.dataset.vendorUuid
          );
        }
      });
    }
  });

  // Universelle Lade-Funktion für Modal und Offcanvas
  async function loadInOverlay(url, title, type, onReadyCallback) {
    const titleEl = type === "modal" ? modalTitle : offcanvasTitle;
    const bodyEl = type === "modal" ? modalBody : offcanvasBody;
    const bsInstance = type === "modal" ? bsModal : bsOffcanvas;

    titleEl.textContent = "Lade...";
    bodyEl.innerHTML =
      '<div class="text-center p-5"><div class="spinner-border"></div></div>';
    if (!bsInstance._isShown) bsInstance.show();

    try {
      const response = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!response.ok)
        throw new Error(`Server antwortete mit Status ${response.status}`);

      const html = await response.text();
      bodyEl.innerHTML = html;
      titleEl.textContent = bodyEl.querySelector("h2,h1")?.textContent || title;

      // Führe eine Callback-Funktion aus, wenn der Inhalt bereit ist
      if (typeof onReadyCallback === "function") {
        onReadyCallback(bodyEl);
      }

      // NEU: Prüfen, ob der geladene Inhalt selbst Skripte initialisieren muss
      const initFunction = bodyEl.querySelector("[data-init-function]")?.dataset
        .initFunction;
      if (initFunction && typeof window[initFunction] === "function") {
        window[initFunction](bodyEl);
      }
    } catch (error) {
      bodyEl.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
  }

  // Ihr AJAX Submit-Handler (bleibt unverändert)
  modalElement.addEventListener("submit", async function (e) {
    if (e.target.tagName === "FORM" && e.target.closest("#ajax-modal")) {
      e.preventDefault();
      const form = e.target;
      const submitButton = form.querySelector('button[type="submit"]');
      if (!submitButton) return;
      const originalButtonText = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Speichere...';
      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const result = await response.json();
        if (!response.ok) throw result;
        displayToast(result.message || "Erfolgreich gespeichert.", "success");
        bsModal.hide();
        setTimeout(() => window.location.reload(), 1500);
      } catch (error) {
        const errorMessage = error.messages
          ? Object.values(error.messages)[0]
          : "Ein Fehler ist aufgetreten.";
        displayToast(errorMessage, "danger");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonText;
        }
      }
    }
  });
});
