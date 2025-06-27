document.addEventListener("DOMContentLoaded", function () {
  const uploaderWrap = document.querySelector(".image-upload-wrap");
  if (!uploaderWrap) return;

  const fileInput = uploaderWrap.querySelector(".file-upload-input");
  const removeButton = document.querySelector(".remove-image");
  const hiddenAvatarInput = document.getElementById("image1_filename");

  // Initialen Zustand der UI setzen
  function updateUI(showImage) {
    if (showImage && hiddenAvatarInput.value) {
      uploaderWrap
        .querySelector(".file-upload-image")
        .setAttribute("src", "/uploads/avatars/" + hiddenAvatarInput.value);
      uploaderWrap.classList.add("image-shown");
      uploaderWrap.querySelector(".file-upload-content").style.display =
        "block";
      uploaderWrap.querySelector(".drag-text").style.display = "none";
    } else {
      uploaderWrap.classList.remove("image-shown");
      uploaderWrap.querySelector(".file-upload-image").setAttribute("src", "#");
      uploaderWrap.querySelector(".file-upload-content").style.display = "none";
      uploaderWrap.querySelector(".drag-text").style.display = "flex";
    }
  }

  // --- Event Listeners ---
  fileInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      const file = this.files[0];
      // Lokale Vorschau sofort anzeigen
      const reader = new FileReader();
      reader.onload = (e) => {
        uploaderWrap
          .querySelector(".file-upload-image")
          .setAttribute("src", e.target.result);
        uploaderWrap.classList.add("image-shown");
        uploaderWrap.querySelector(".file-upload-content").style.display =
          "block";
        uploaderWrap.querySelector(".drag-text").style.display = "none";
      };
      reader.readAsDataURL(file);
      uploadFile(file);
    }
  });

  removeButton.addEventListener("click", function (e) {
    e.preventDefault();
    const filenameToDelete = hiddenAvatarInput.value;

    hiddenAvatarInput.value = ""; // Wert im Formular sofort leeren
    fileInput.value = ""; // File-Input zurücksetzen
    updateUI(false); // UI auf Anfangszustand setzen

    if (filenameToDelete) {
      // Löschanfrage an den Server senden (Feuer und Vergiss)
      deleteFileOnServer(filenameToDelete);
    }
  });

  // --- AJAX Funktionen ---
  function uploadFile(file) {
    const progressBar = uploaderWrap.querySelector(".progress-bar");
    const formData = new FormData();
    formData.append("image", file);
    const csrfToken = document.querySelector(".csrf-token");
    if (csrfToken) {
      formData.append(csrfToken.name, csrfToken.value);
    }

    uploaderWrap.querySelector(".progress-bar-wrap").style.display = "block";
    progressBar.style.width = "0%";

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/avatar-upload", true);

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable)
        progressBar.style.width = (e.loaded / e.total) * 100 + "%";
    };

    xhr.onload = () => {
      uploaderWrap.querySelector(".progress-bar-wrap").style.display = "none";
      if (xhr.status === 201) {
        // Nur bei Erfolg den Wert im Hidden-Input setzen
        hiddenAvatarInput.value = JSON.parse(xhr.responseText).filename;
      } else {
        alert("Bild-Upload fehlgeschlagen!");
        // Bei Fehler UI zurücksetzen, aber den alten Wert wiederherstellen
        const oldValue = uploaderWrap
          .querySelector(".file-upload-image")
          .src.split("/")
          .pop();
        hiddenAvatarInput.value = oldValue !== "#" ? oldValue : "";
        updateUI(!!hiddenAvatarInput.value);
      }
    };

    xhr.onerror = () => {
      alert("Netzwerkfehler.");
      updateUI(!!hiddenAvatarInput.value);
    };

    xhr.send(formData);
  }

  async function deleteFileOnServer(filename) {
    try {
      const csrfToken = document.querySelector(".csrf-token");
      await fetch("/api/rating-image-delete", {
        // Hinweis: Dies sollte /api/avatar-delete sein
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": csrfToken.value,
        },
        body: JSON.stringify({ filename: filename }),
      });
    } catch (error) {
      console.error("Fehler beim Löschen des Avatars auf dem Server:", error);
    }
  }

  // Beim Laden der Seite initialen Zustand herstellen
  updateUI(true);
});
