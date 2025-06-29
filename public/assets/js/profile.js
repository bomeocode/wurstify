document.addEventListener("DOMContentLoaded", function () {
  console.log("Profil-Skript (mit globaler Modal-Logik) geladen.");

  // ========================================================
  // === TEIL 1: LOGIK NUR FÜR DIE PROFILSEITE (AVATAR-UPLOAD)
  // ========================================================
  const profileForm = document.querySelector('form[action*="profile/update"]');
  // Wir führen den Uploader-Code nur aus, wenn wir auf der Profilseite sind.
  if (profileForm) {
    const fileInput = document.getElementById("avatar-input");
    const avatarPreview = document.getElementById("avatar-preview");
    const removeButton = document.getElementById("remove-avatar-btn");
    const hiddenAvatarInput = document.getElementById("avatar-filename-input");
    const saveDetailsButton = document.getElementById("save-details-btn");
    const progressBar = document.getElementById("progress-bar");
    const progressWrap = document.getElementById("progress-wrap");
    const uploadLabel = document.querySelector('label[for="avatar-input"]');
    const initialAvatarSrc = avatarPreview ? avatarPreview.src : "";

    if (fileInput) {
      fileInput.addEventListener("change", function () {
        if (this.files && this.files[0]) {
          const file = this.files[0];
          const reader = new FileReader();
          reader.onload = (e) => {
            if (avatarPreview) avatarPreview.src = e.target.result;
          };
          reader.readAsDataURL(file);
          uploadFile(file);
        }
      });
    }

    if (removeButton) {
      removeButton.addEventListener("click", function () {
        if (fileInput) fileInput.value = "";
        if (hiddenAvatarInput) hiddenAvatarInput.value = "";
        if (avatarPreview)
          avatarPreview.src = "/assets/img/avatar-placeholder.png";
      });
    }

    function uploadFile(file) {
      const formData = new FormData();
      formData.append("image", file);
      const csrfInput = profileForm.querySelector('input[name^="csrf_"]');
      if (csrfInput) {
        formData.append(csrfInput.name, csrfInput.value);
      } else {
        displayToast("Sicherheitsfehler. Bitte Seite neu laden.", "danger");
        return;
      }

      setControlsDisabled(true);
      if (progressWrap) progressWrap.style.display = "block";
      if (progressBar) progressBar.style.width = "0%";

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "/api/avatar-upload", true);

      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable && progressBar) {
          progressBar.style.width = (e.loaded / e.total) * 100 + "%";
        }
      };

      xhr.onload = () => {
        setControlsDisabled(false);
        if (progressWrap) progressWrap.style.display = "none";

        if (xhr.status === 201) {
          if (hiddenAvatarInput)
            hiddenAvatarInput.value = JSON.parse(xhr.responseText).filename;
          displayToast("Profilbild erfolgreich hochgeladen.", "success");
        } else {
          let errorMessage = "Upload fehlgeschlagen.";
          try {
            errorMessage =
              JSON.parse(xhr.responseText).messages.image || errorMessage;
          } catch (e) {}
          displayToast(errorMessage, "danger");
          if (avatarPreview) avatarPreview.src = initialAvatarSrc;
        }
      };

      xhr.onerror = () => {
        setControlsDisabled(false);
        if (progressWrap) progressWrap.style.display = "none";
        displayToast("Netzwerkfehler.", "danger");
        if (avatarPreview) avatarPreview.src = initialAvatarSrc;
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
  }

  // ========================================================
  // === TEIL 2: GLOBALE LOGIK FÜR MODALS (WIRD IMMER GEBRAUCHT)
  // ========================================================
  const modalElement = document.getElementById("ajax-modal");
  if (!modalElement) return;

  const modalTitle = modalElement.querySelector(".modal-title");
  const modalBody = modalElement.querySelector(".modal-body");
  const bsModal = new bootstrap.Modal(modalElement);

  // Globaler Klick-Listener für alle Modal-Trigger
  document.addEventListener("click", async function (e) {
    const modalTrigger = e.target.closest(".open-modal-form");
    if (modalTrigger) {
      e.preventDefault();
      const url = modalTrigger.dataset.url;
      modalTitle.textContent = "Lade...";
      modalBody.innerHTML =
        '<div class="text-center p-5"><div class="spinner-border"></div></div>';
      bsModal.show();
      try {
        const response = await fetch(url, {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        if (!response.ok)
          throw new Error("Inhalt konnte nicht geladen werden.");
        modalBody.innerHTML = await response.text();
        modalTitle.textContent =
          modalBody.querySelector("h2,h1")?.textContent || "Information";
      } catch (error) {
        modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
      }
    }
  });

  // Globaler Submit-Listener für Formulare im Modal
  modalElement.addEventListener("submit", async function (e) {
    if (e.target.tagName === "FORM" && e.target.closest("#ajax-modal")) {
      e.preventDefault();
      const form = e.target;
      const submitButton = form.querySelector('button[type="submit"]');
      const originalButtonText = submitButton
        ? submitButton.innerHTML
        : "Senden";
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML =
          '<span class="spinner-border spinner-border-sm"></span> Sende...';
      }
      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const result = await response.json();
        if (!response.ok) throw result;
        displayToast(result.message || "Aktion erfolgreich.", "success");
        bsModal.hide();
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

  // Globale Toast-Funktion
  function displayToast(message, type = "success") {
    const container = document.querySelector(".toast-container");
    if (!container) {
      alert(message);
      return;
    }
    const toastId = "toast-" + Date.now();
    const toastHTML = `<div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
    container.insertAdjacentHTML("beforeend", toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    toastElement.addEventListener("hidden.bs.toast", () =>
      toastElement.remove()
    );
  }
});
