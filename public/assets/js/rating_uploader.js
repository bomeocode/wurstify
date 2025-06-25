// public/js/rating_uploader.js

document.addEventListener("DOMContentLoaded", () => {
  // Fügt allen Datei-Inputs einen Event Listener hinzu
  document.querySelectorAll(".file-upload-input").forEach((input) => {
    input.addEventListener("change", function () {
      // 'this' bezieht sich auf das input-Element, das geändert wurde
      if (this.files && this.files[0]) {
        const slot = this.dataset.slot;
        handleFileUpload(this, slot);
      }
    });
  });

  // Fügt allen "Entfernen"-Buttons einen Event Listener hinzu
  document.querySelectorAll(".remove-image").forEach((button) => {
    button.addEventListener("click", function () {
      const slot = this.dataset.slot;
      removeUpload(slot);
    });
  });
});

function handleFileUpload(inputElement, slot) {
  const file = inputElement.files[0];
  const wrap = document.querySelector(
    `.image-upload-wrap[data-slot="${slot}"]`
  );
  const imageElement = wrap.querySelector(".file-upload-image");
  const contentElement = wrap.querySelector(".file-upload-content");
  const dragTextElement = wrap.querySelector(".drag-text");

  // Zeige sofort eine lokale Vorschau des Bildes an
  const reader = new FileReader();
  reader.onload = function (e) {
    imageElement.setAttribute("src", e.target.result);
    contentElement.style.display = "block";
    dragTextElement.style.display = "none";
  };
  reader.readAsDataURL(file);

  // Starte den AJAX-Upload im Hintergrund
  uploadFile(file, slot);
}

function removeUpload(slot) {
  const wrap = document.querySelector(
    `.image-upload-wrap[data-slot="${slot}"]`
  );
  const inputElement = wrap.querySelector(".file-upload-input");
  const contentElement = wrap.querySelector(".file-upload-content");
  const dragTextElement = wrap.querySelector(".drag-text");
  const imageElement = wrap.querySelector(".file-upload-image");
  const hiddenInput = document.getElementById(`image${slot}_filename`);

  inputElement.value = ""; // Wichtig, um die gleiche Datei erneut auswählen zu können
  contentElement.style.display = "none";
  dragTextElement.style.display = "block";
  imageElement.setAttribute("src", "#");
  hiddenInput.value = "";
  // Optional: Fortschrittsanzeige zurücksetzen
  wrap.querySelector(".progress-bar").style.width = "0%";
}

function uploadFile(file, slot) {
  const wrap = document.querySelector(
    `.image-upload-wrap[data-slot="${slot}"]`
  );
  const progressBarWrap = wrap.querySelector(".progress-bar-wrap");
  const progressBar = wrap.querySelector(".progress-bar");
  const hiddenInput = document.getElementById(`image${slot}_filename`);

  const formData = new FormData();
  formData.append("image", file);

  progressBarWrap.style.display = "block";
  progressBar.style.width = "0%";

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "/api/rating-image-upload", true);

  // Fortschrittsanzeige
  xhr.upload.onprogress = function (e) {
    if (e.lengthComputable) {
      const percentComplete = (e.loaded / e.total) * 100;
      progressBar.style.width = percentComplete + "%";
    }
  };

  // Upload abgeschlossen
  xhr.onload = function () {
    progressBarWrap.style.display = "none";
    if (xhr.status === 201) {
      // 201 Created ist die korrekte Antwort von unserem API Controller
      const data = JSON.parse(xhr.responseText);
      hiddenInput.value = data.filename;
      console.log("Upload erfolgreich, Dateiname:", data.filename);
    } else {
      console.error("Upload fehlgeschlagen:", xhr.responseText);
      alert(
        "Bild-Upload fehlgeschlagen! Bitte stellen Sie sicher, dass das Bild unter 2MB groß ist und ein gängiges Format hat (jpg, png)."
      );
      removeUpload(slot);
    }
  };

  // Fehlerbehandlung
  xhr.onerror = function () {
    progressBarWrap.style.display = "none";
    alert("Ein Netzwerkfehler ist aufgetreten. Bitte erneut versuchen.");
    removeUpload(slot);
  };

  xhr.send(formData);
}

// Kleine Anpassung für die CSS-Klassen der Platzhalter, um sie eindeutig zu machen
document.querySelectorAll(".image-upload-wrap").forEach((wrap, index) => {
  wrap.dataset.slot = index + 1;
});

document.querySelectorAll(".remove-image").forEach((button, index) => {
  button.dataset.slot = index + 1;
});
