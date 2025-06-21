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
