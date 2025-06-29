document.addEventListener("DOMContentLoaded", function () {
  console.log("Bewertungs-Skript (komplett) geladen und bereit.");

  // ===================================================================
  // 1. SETUP: Alle DOM-Elemente einmalig holen
  // ===================================================================

  // --- Elemente für die Anbietersuche ---
  const useLocationBtn = document.getElementById("use-current-location");
  const addressField = document.getElementById("address_manual");
  const vendorNameField = document.getElementById("vendor_name");
  const suggestionsContainer = document.getElementById("vendor-suggestions");
  const statusText = document.getElementById("location-status");

  // --- Elemente für den Bild-Uploader ---
  const imageUploadWraps = document.querySelectorAll(".image-upload-wrap");

  // ===================================================================
  // 2. EVENT LISTENERS ZUWEISEN
  // ===================================================================

  // --- Listener für die Anbietersuche ---
  if (useLocationBtn) {
    useLocationBtn.addEventListener("click", handleGeoLocation);
  }

  let debounceTimer;
  if (addressField) {
    addressField.addEventListener("keyup", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(handleAddressInput, 500);
    });
  }

  // --- Listener für den Bild-Uploader ---
  imageUploadWraps.forEach((wrap) => {
    const input = wrap.querySelector(".file-upload-input");
    if (input) {
      input.addEventListener("change", function () {
        if (this.files && this.files[0]) {
          handleFileUpload(this, this.dataset.slot);
        }
      });
    }
  });

  document.addEventListener("click", function (event) {
    const removeButton = event.target.closest(".remove-image");
    if (removeButton) {
      event.preventDefault();
      removeUpload(removeButton.dataset.slot);
    }
  });

  // ===================================================================
  // 3. FUNKTIONEN
  // ===================================================================

  // --- Funktionen für die Anbietersuche ---
  function handleGeoLocation() {
    if (!("geolocation" in navigator)) {
      statusText.textContent =
        "Ortungsdienste werden von Ihrem Browser nicht unterstützt.";
      return;
    }
    statusText.textContent = "Standort wird ermittelt...";
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        statusText.textContent = "Suche Anbieter und Adresse...";
        fetchVendors(`lat=${latitude}&lon=${longitude}`);
        fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
        )
          .then((response) => response.json())
          .then((data) => {
            if (data && data.display_name) {
              addressField.value = data.display_name;
            }
          });
      },
      (error) => {
        let message = "Fehler bei der Ortung.";
        if (error.code === 1) message = "Zugriff auf Standort verweigert.";
        statusText.textContent = message;
      }
    );
  }

  function handleAddressInput() {
    const query = addressField.value;
    if (query.length < 3) {
      suggestionsContainer.innerHTML = "";
      return;
    }
    statusText.textContent = "Suche nach passenden Anbietern...";
    fetchVendors(`q=${encodeURIComponent(query)}`);
  }

  async function fetchVendors(params) {
    try {
      const response = await fetch(`/api/vendor-search?${params}`);
      const vendors = await response.json();
      renderSuggestions(vendors);
    } catch (error) {
      console.error("Fehler beim Holen der Anbieter:", error);
      statusText.textContent = "Suche fehlgeschlagen.";
    }
  }

  function renderSuggestions(vendors) {
    suggestionsContainer.innerHTML = "";
    if (vendors.length === 0) {
      statusText.textContent =
        "Keine bekannten Anbieter für diesen Ort gefunden.";
      return;
    }
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
        vendorNameField.value = vendor.name;
        addressField.value = vendor.address || "";
        suggestionsContainer.innerHTML = "";
        statusText.textContent = `Anbieter "${vendor.name}" ausgewählt.`;
      });
      suggestionsContainer.appendChild(link);
    });
  }

  // --- Funktionen für den Bild-Uploader ---
  function handleFileUpload(inputElement, slot) {
    const file = inputElement.files[0];
    const wrap = document.querySelector(
      `.image-upload-wrap[data-slot="${slot}"]`
    );
    if (!wrap || !file) return;

    // NEU: Setze die Klasse, um das Input-Feld zu deaktivieren
    wrap.classList.add("image-shown");

    const reader = new FileReader();
    reader.onload = (e) => {
      wrap
        .querySelector(".file-upload-image")
        .setAttribute("src", e.target.result);
      wrap.querySelector(".file-upload-content").style.display = "block";
      wrap.querySelector(".drag-text").style.display = "none";
    };
    reader.readAsDataURL(file);

    uploadFile(file, slot);
  }

  function removeUpload(slot) {
    const wrap = document.querySelector(
      `.image-upload-wrap[data-slot="${slot}"]`
    );
    const hiddenInput = document.getElementById(`image${slot}_filename`);
    const filenameToDelete = hiddenInput.value;

    // NEU: Entferne die Klasse, um das Input-Feld wieder klickbar zu machen
    wrap.classList.remove("image-shown");

    // Reset der UI
    wrap.querySelector(".file-upload-input").value = "";
    wrap.querySelector(".file-upload-content").style.display = "none";
    wrap.querySelector(".drag-text").style.display = "flex";
    wrap.querySelector(".file-upload-image").setAttribute("src", "#");
    hiddenInput.value = "";
    wrap.querySelector(".progress-bar").style.width = "0%";

    // Wenn eine Datei auf dem Server war, Löschanfrage senden
    if (filenameToDelete) {
      // ... (Logik zum Löschen auf dem Server) ...
      console.log(
        `Löschanfrage für ${filenameToDelete} müsste gesendet werden.`
      );
    }
  }

  function uploadFile(file, slot) {
    const wrap = document.querySelector(
      `.image-upload-wrap[data-slot="${slot}"]`
    );
    const progressBar = wrap.querySelector(".progress-bar");
    const hiddenInput = document.getElementById(`image${slot}_filename`);
    const formData = new FormData();
    formData.append("image", file);
    const csrfToken = document.querySelector(".csrf-token");
    if (csrfToken) {
      formData.append(csrfToken.name, csrfToken.value);
    }

    wrap.querySelector(".progress-bar-wrap").style.display = "block";
    progressBar.style.width = "0%";

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/rating-image-upload", true);
    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable)
        progressBar.style.width = (e.loaded / e.total) * 100 + "%";
    };
    xhr.onload = () => {
      wrap.querySelector(".progress-bar-wrap").style.display = "none";
      if (xhr.status === 201) {
        hiddenInput.value = JSON.parse(xhr.responseText).filename;
      } else {
        alert("Bild-Upload fehlgeschlagen!");
        removeUpload(slot);
      }
    };
    xhr.onerror = () => {
      alert("Netzwerkfehler.");
      removeUpload(slot);
    };
    xhr.send(formData);
  }

  // --- Initialisierung bei Seitenaufruf ---
  imageUploadWraps.forEach((wrap, index) => {
    const slotNumber = index + 1;
    wrap.dataset.slot = slotNumber;
    const fileInput = wrap.querySelector(".file-upload-input");
    if (fileInput) fileInput.dataset.slot = slotNumber;
    const removeBtn = wrap.querySelector(".remove-image");
    if (removeBtn) removeBtn.dataset.slot = slotNumber;
  });
});
