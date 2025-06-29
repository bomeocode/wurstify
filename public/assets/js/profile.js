document.addEventListener("DOMContentLoaded", function () {
  // === SETUP ===
  const fileInput = document.getElementById("avatar-input");
  const avatarPreview = document.getElementById("avatar-preview");
  const removeButton = document.getElementById("remove-avatar-btn");
  const hiddenAvatarInput = document.getElementById("avatar-filename-input");
  const saveDetailsButton = document.getElementById("save-details-btn");
  const progressBar = document.getElementById("progress-bar");
  const progressWrap = document.getElementById("progress-wrap");
  const csrfInput = document.querySelector(
    'form[action*="profile/update"] input[name^="csrf_"]'
  );
  const uploadLabel = document.querySelector('label[for="avatar-input"]');

  if (!fileInput) return;

  const initialAvatarSrc = avatarPreview.src;

  // === EVENT LISTENERS ===
  fileInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        avatarPreview.src = e.target.result;
      };
      reader.readAsDataURL(this.files[0]);
      uploadFile(this.files[0]);
    }
  });

  removeButton.addEventListener("click", function () {
    fileInput.value = "";
    hiddenAvatarInput.value = "";
    avatarPreview.src = "/assets/img/avatar-placeholder.png";
  });

  // === FUNKTIONEN ===
  function uploadFile(file) {
    const formData = new FormData();
    formData.append("image", file);
    if (csrfInput) formData.append(csrfInput.name, csrfInput.value);

    setControlsDisabled(true);
    progressWrap.style.display = "block";
    progressBar.style.width = "0%";

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/avatar-upload", true);
    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        progressBar.style.width = (e.loaded / e.total) * 100 + "%";
      }
    };

    xhr.onload = () => {
      setControlsDisabled(false);
      progressWrap.style.display = "none";

      if (xhr.status === 201) {
        hiddenAvatarInput.value = JSON.parse(xhr.responseText).filename;
        // HIER IST DIE ÄNDERUNG: Erfolgs-Toast anzeigen
        displayToast("Profilbild erfolgreich hochgeladen.", "success");
      } else {
        let errorMessage = "Upload fehlgeschlagen.";
        try {
          errorMessage =
            JSON.parse(xhr.responseText).messages.image || errorMessage;
        } catch (e) {}
        displayToast(errorMessage, "danger");
        avatarPreview.src = initialAvatarSrc;
      }
    };

    xhr.onerror = () => {
      setControlsDisabled(false);
      progressWrap.style.display = "none";
      displayToast("Netzwerkfehler beim Upload.", "danger");
    };

    xhr.send(formData);
  }

  function setControlsDisabled(disabled) {
    if (saveDetailsButton) saveDetailsButton.disabled = disabled;
    if (removeButton) removeButton.disabled = disabled;
    if (uploadLabel) {
      disabled
        ? uploadLabel.classList.add("disabled")
        : uploadLabel.classList.remove("disabled");
    }
    if (fileInput) fileInput.disabled = disabled;
  }

  // In sich geschlossene Toast-Funktion
  function displayToast(message, type = "success") {
    const container = document.querySelector(".toast-container");
    if (!container) {
      alert(message);
      return;
    } // Notfall-Fallback
    const toastId = "toast-" + Date.now();
    const toastHTML = `<div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
    container.insertAdjacentHTML("beforeend", toastHTML);
    const toastElement = document.getElementById(toastId);
    if (toastElement) {
      const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
      toast.show();
      toastElement.addEventListener("hidden.bs.toast", () =>
        toastElement.remove()
      );
    }
  }
});
