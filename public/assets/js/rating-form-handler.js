// Diese Datei enthält die gesamte Logik für das Bewertungsformular.
// Die Hauptfunktion wird exportiert, um von anderen Skripten importiert zu werden.

/**
 * Initialisiert alle interaktiven Skripte für das Bewertungsformular.
 * @param {HTMLElement} container - Das DOM-Element, das das Formular enthält (z.B. der modal-body).
 * @param {Function} showToast - Eine Funktion zum Anzeigen von Toast-Benachrichtigungen.
 */
export function initializeRatingFormScripts(container, showToast) {
  // --- Setup der Elemente innerhalb des Containers ---
  // console.log("[DEBUG] A: initializeRatingFormScripts gestartet.");

  const useLocationBtn = container.querySelector("#use-current-location");
  const addressField = container.querySelector("#address_manual");
  const vendorNameField = container.querySelector("#vendor_name");
  const suggestionsContainer = container.querySelector("#vendor-suggestions");
  const statusText = container.querySelector("#location-status");

  // --- Logik für Standort-Button & Vendor Suggestions ---
  if (useLocationBtn && addressField) {
    useLocationBtn.addEventListener("click", () => {
      if (statusText) statusText.textContent = "Standort wird ermittelt...";
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          if (statusText)
            statusText.textContent = "Suche Anbieter und Adresse...";
          fetchVendors(`lat=${latitude}&lon=${longitude}`);
          fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
          )
            .then((res) => res.json())
            .then((data) => {
              if (data.display_name) addressField.value = data.display_name;
            });
        },
        () => {
          if (statusText)
            statusText.textContent = "Standort konnte nicht ermittelt werden.";
        }
      );
    });
  }

  let debounceTimer;
  if (addressField) {
    addressField.addEventListener("keyup", () => {
      clearTimeout(debounceTimer);
      if (addressField.value.length < 3) {
        if (suggestionsContainer) suggestionsContainer.innerHTML = "";
        return;
      }
      debounceTimer = setTimeout(() => {
        if (statusText)
          statusText.textContent = "Suche nach passenden Anbietern...";
        fetchVendors(`q=${encodeURIComponent(addressField.value)}`);
      }, 500);
    });
  }

  async function fetchVendors(params) {
    try {
      const response = await fetch(`/api/vendor-search?${params}`);
      renderSuggestions(await response.json());
    } catch (e) {
      console.error("Fehler beim Holen der Anbieter:", e);
    }
  }

  function renderSuggestions(vendors) {
    if (!suggestionsContainer) return;
    suggestionsContainer.innerHTML = "";
    if (vendors.length === 0) {
      if (statusText)
        statusText.textContent = "Keine bekannten Anbieter gefunden.";
      return;
    }
    if (statusText)
      statusText.textContent = `${vendors.length} Anbieter gefunden:`;
    vendors.forEach((vendor) => {
      const link = document.createElement("a");
      link.href = "#";
      link.className = "list-group-item list-group-item-action";
      link.textContent = `${vendor.name} (${
        vendor.address || "Adresse unbekannt"
      })`;
      link.addEventListener("click", (e) => {
        e.preventDefault();
        if (vendorNameField) vendorNameField.value = vendor.name;
        if (addressField) addressField.value = vendor.address || "";
        suggestionsContainer.innerHTML = "";
        const categoryBlock = container.querySelector("#vendor-category-block");
        if (categoryBlock) {
          categoryBlock.style.display = "none";
        }
      });
      suggestionsContainer.appendChild(link);
    });
  }

  // --- Logik für den Bilder-Uploader ---
  container.querySelectorAll(".rating-image-upload-wrap").forEach((wrap) => {
    const fileInput = wrap.querySelector(".rating-file-upload-input");
    const removeBtn = wrap.querySelector(".rating-remove-image");
    const slot = wrap.dataset.slot;

    if (fileInput) {
      fileInput.addEventListener("change", function () {
        // console.log(
        //   `%c[DEBUG] C: DATEI AUSGEWÄHLT in Slot ${slot}!`,
        //   "background: #28a745; color: white;"
        // );
        if (this.files[0]) handleFileUpload(this, container);
      });
    }
    if (removeBtn) {
      removeBtn.addEventListener("click", () =>
        removeUpload(wrap.dataset.slot, container)
      );
    }
  });

  function handleFileUpload(inputElement, container) {
    const file = inputElement.files[0];
    const slot = inputElement.closest(".rating-image-upload-wrap").dataset.slot;
    const wrap = container.querySelector(
      `.rating-image-upload-wrap[data-slot="${slot}"]`
    );
    // console.log(
    //   `[DEBUG] D: handleFileUpload wird für Slot ${slot} ausgeführt.`
    // );

    if (!wrap || !file) return;

    wrap.classList.add("image-shown");
    const reader = new FileReader();
    reader.onload = (e) => {
      const imgElement = wrap.querySelector(".rating-file-upload-image"); // KORRIGIERTER SELEKTOR
      // console.log(
      //   `[DEBUG] E: Suche nach .rating-file-upload-image für lokale Vorschau. Gefunden:`,
      //   imgElement
      // );
      if (imgElement) {
        imgElement.src = e.target.result;
      }
    };
    reader.readAsDataURL(file);
    uploadRatingFile(file, slot, container, showToast);
  }

  function removeUpload(slot, container) {
    const wrap = container.querySelector(
      `.rating-image-upload-wrap[data-slot="${slot}"]`
    );
    if (!wrap) return;
    wrap.classList.remove("image-shown");
    wrap.querySelector(".rating-file-upload-input").value = "";
    wrap.querySelector(".rating-file-upload-image").src = "#";
    const hiddenInput = container.querySelector(`#image${slot}_filename`);
    if (hiddenInput) hiddenInput.value = "";
  }

  function uploadRatingFile(file, slot, container, showToast) {
    const formData = new FormData();
    formData.append("image", file);
    const csrfInput = container.querySelector('form input[name^="csrf_"]');

    if (csrfInput) {
      formData.append(csrfInput.name, csrfInput.value);
    } else {
      console.error("CSRF Token nicht im Formular gefunden!");
      showToast("Sicherheitsfehler. Bitte Seite neu laden.", "danger");
      return;
    }

    // NEU: Rufen die Helfer-Funktion auf, um alles zu deaktivieren
    setFormControlsDisabled(container, true);
    const wrap = container.querySelector(
      `.rating-image-upload-wrap[data-slot="${slot}"]`
    );
    const progressBar = wrap.querySelector(".rating-progress-bar");
    const progressWrap = wrap.querySelector(".rating-progress-bar-wrap");

    // Mache den Ladebalken-Container sichtbar und setze ihn auf 0%
    if (progressWrap) progressWrap.style.display = "block";
    if (progressBar) progressBar.style.width = "0%";

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/rating-image-upload", true);

    // NEU: Fortschrittsbalken-Logik wiederherstellen
    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable && progressBar) {
        const percentComplete = (e.loaded / e.total) * 100;
        progressBar.style.width = percentComplete + "%";
      }
    };

    xhr.onload = function () {
      setFormControlsDisabled(container, false);
      if (progressWrap) progressWrap.style.display = "none";

      if (xhr.status === 201) {
        const data = JSON.parse(xhr.responseText);
        const hiddenInput = container.querySelector(`#image${slot}_filename`);
        const imagePreview = container.querySelector(
          `.rating-image-upload-wrap[data-slot="${slot}"] .rating-file-upload-image`
        );

        if (hiddenInput) hiddenInput.value = data.filename;
        if (imagePreview)
          imagePreview.src = "/uploads/ratings/" + data.filename;
        showToast("Bild erfolgreich hochgeladen", "success");
      } else {
        let errorMessage = "Upload fehlgeschlagen!";
        try {
          errorMessage =
            JSON.parse(xhr.responseText).messages.image || errorMessage;
        } catch (e) {}
        showToast(errorMessage, "danger");
        removeUpload(slot, container);
      }
    };

    xhr.onerror = function () {
      // NEU: Buttons auch bei Netzwerkfehler wieder aktivieren
      setFormControlsDisabled(container, false);
      if (progressWrap) progressWrap.style.display = "none";
      showToast("Netzwerkfehler beim Upload.", "danger");
    };

    xhr.send(formData);
  }

  // NEU: Die zentrale Helfer-Funktion zum Deaktivieren der Elemente
  function setFormControlsDisabled(container, disabled) {
    const submitButton = container.querySelector('button[type="submit"]');
    const uploadLabels = container.querySelectorAll(
      ".rating-image-upload-wrap label"
    );
    const fileInputs = container.querySelectorAll(".rating-file-upload-input");
    const removeButtons = container.querySelectorAll(".rating-remove-image");

    if (submitButton) submitButton.disabled = disabled;
    fileInputs.forEach((input) => (input.disabled = disabled));
    removeButtons.forEach((button) => (button.disabled = disabled));

    // Um die "Ändern"-Buttons visuell auszugrauen
    if (uploadLabels) {
      uploadLabels.forEach((label) => {
        disabled
          ? label.classList.add("disabled")
          : label.classList.remove("disabled");
      });
    }
  }
}
