/**
 * ACMS Core Application Script
 * Externalized event listeners, PDF generation, kiosk scanner logic, & GPS Geofencing
 */

// Global Geofencing Configuration for Abbey Mortgage Bank Building, Okota Road, Lagos
const ACMS_GEOFENCE = {
  OFFICE_LAT: 6.5198972285145596,
  OFFICE_LNG: 3.3180273545580072,
  MAX_RADIUS_METERS: 75, // 75m allowance for indoor GPS drift
};

/**
 * Calculates straight-line distance between two coordinates in meters (Haversine Formula)
 */
function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
  const R = 6371e3; // Earth radius in meters
  const rad = Math.PI / 180;
  const dLat = (lat2 - lat1) * rad;
  const dLon = (lon2 - lon1) * rad;

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * rad) *
      Math.cos(lat2 * rad) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

/**
 * Verifies staff browser position before processing clock-in logic
 */
function verifyOnSiteLocation(onSuccessCallback, onErrorCallback) {
  if (!navigator.geolocation) {
    alert(
      "Browser geolocation is required to verify attendance location, but it is not supported by your browser.",
    );
    if (onErrorCallback) onErrorCallback();
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const userLat = position.coords.latitude;
      const userLng = position.coords.longitude;
      const distance = calculateDistanceMeters(
        ACMS_GEOFENCE.OFFICE_LAT,
        ACMS_GEOFENCE.OFFICE_LNG,
        userLat,
        userLng,
      );

      if (distance <= ACMS_GEOFENCE.MAX_RADIUS_METERS) {
        onSuccessCallback(userLat, userLng);
      } else {
        const roundedDist = Math.round(distance);
        alert(
          `Clock-in Denied: You are ${roundedDist} meters away from the office. You must be on-site at Okota Road to clock in.`,
        );
        if (onErrorCallback) onErrorCallback();
      }
    },
    (error) => {
      let msg = "Unable to verify location.";
      if (error.code === error.PERMISSION_DENIED) {
        msg =
          "Location permission denied. Please enable location access in browser settings to clock in.";
      } else if (error.code === error.POSITION_UNAVAILABLE) {
        msg = "Location details unavailable. Please enable device GPS.";
      } else if (error.code === error.TIMEOUT) {
        msg = "Location verification timed out. Please try again.";
      }
      alert(msg);
      if (onErrorCallback) onErrorCallback();
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0,
    },
  );
}

document.addEventListener("DOMContentLoaded", () => {
  /* ==========================================================================
       1. REAL-TIME SYSTEM CLOCK (Dashboard & Header)
       ========================================================================== */
  const timeDisplay = document.getElementById("current-system-time");
  if (timeDisplay) {
    function updateSystemClock() {
      const now = new Date();
      timeDisplay.textContent =
        now.toLocaleDateString("en-US", {
          weekday: "short",
          month: "short",
          day: "numeric",
        }) +
        " | " +
        now.toLocaleTimeString("en-US", {
          hour: "2-digit",
          minute: "2-digit",
          hour12: true,
        });
    }
    updateSystemClock();
    setInterval(updateSystemClock, 1000);
  }

  /* ==========================================================================
       2. DOUBLE-SIDED ID CARD PRINTING & PDF DOWNLOAD ENGINE
       ========================================================================== */
  const printBtn = document.getElementById("printBadgeBtn");
  if (printBtn) {
    printBtn.addEventListener("click", (e) => {
      e.preventDefault();
      window.print();
    });
  }

  const pdfBtn = document.getElementById("downloadPdfBtn");
  if (pdfBtn) {
    pdfBtn.addEventListener("click", (e) => {
      e.preventDefault();

      if (typeof html2pdf === "undefined") {
        alert(
          "PDF generator library is loading. Please try again in a moment.",
        );
        return;
      }

      const badgeElement = document.querySelector(".badge-print-container");
      if (!badgeElement) {
        alert("Badge container element not found.");
        return;
      }

      const staffNameElem = document.querySelector(".staff-fullname");
      const staffName = staffNameElem
        ? staffNameElem.textContent.trim().replace(/\s+/g, "_")
        : "Staff_Badge";

      const opt = {
        margin: 8,
        filename: `${staffName}_ID_Badge.pdf`,
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          allowTaint: true,
          logging: false,
        },
        jsPDF: { unit: "mm", format: "a4", orientation: "landscape" },
      };

      const originalText = pdfBtn.innerHTML;
      pdfBtn.disabled = true;
      pdfBtn.innerHTML =
        '<i class="fa fa-spinner fa-spin me-1"></i> Generating PDF...';

      html2pdf()
        .set(opt)
        .from(badgeElement)
        .save()
        .then(() => {
          pdfBtn.disabled = false;
          pdfBtn.innerHTML = originalText;
        })
        .catch((err) => {
          console.error("PDF generation error:", err);
          pdfBtn.disabled = false;
          pdfBtn.innerHTML = originalText;
          alert(
            "Could not generate PDF directly. You can click 'Print' and select 'Save as PDF'.",
          );
        });
    });
  }

  /* ==========================================================================
       3. UNIFIED DELETE CONFIRMATION INTERCEPTOR
       ========================================================================== */
  const deleteButtons = document.querySelectorAll(".delete-action-btn");
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const message =
        btn.getAttribute("data-confirm") ||
        "Are you sure you want to erase this record?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  /* ==========================================================================
       4. QR ATTENDANCE TERMINAL KIOSK & CAMERA PIPELINE
       ========================================================================== */
  const kioskForm = document.getElementById("kioskForm");
  const readerElem = document.getElementById("reader");

  if (kioskForm && readerElem && typeof Html5Qrcode !== "undefined") {
    const html5QrCode = new Html5Qrcode("reader");
    const qrConfig = { fps: 10, qrbox: { width: 250, height: 250 } };
    let isCameraScanning = false;
    let isSubmissionInProgress = false;

    // Web Audio API Tones
    function triggerAudioNotification(statusTone) {
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;

        const context = new AudioCtx();
        const oscillator = context.createOscillator();
        const gainNode = context.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(context.destination);

        if (statusTone === "success") {
          oscillator.type = "sine";
          oscillator.frequency.setValueAtTime(587.33, context.currentTime);
          oscillator.frequency.setValueAtTime(880.0, context.currentTime + 0.1);
          gainNode.gain.setValueAtTime(0.15, context.currentTime);
          oscillator.start();
          oscillator.stop(context.currentTime + 0.25);
        } else if (statusTone === "danger") {
          oscillator.type = "sawtooth";
          oscillator.frequency.setValueAtTime(260.0, context.currentTime);
          gainNode.gain.setValueAtTime(0.4, context.currentTime);
          gainNode.gain.linearRampToValueAtTime(
            0.01,
            context.currentTime + 0.4,
          );
          oscillator.start();
          oscillator.stop(context.currentTime + 0.4);
        }
      } catch (error) {
        console.error("Audio system blocked:", error);
      }
    }

    // Real-Time Offline Monitor
    function evaluateNetworkConnectivity() {
      const offlineBanner = document.getElementById("networkOfflineAlert");
      const manualButton = document.getElementById("manualSubmitBtn");
      const manualInput = document.getElementById("manual_staff_id");

      if (!navigator.onLine) {
        if (offlineBanner) offlineBanner.style.display = "block";
        if (manualButton) manualButton.disabled = true;
        if (manualInput) manualInput.disabled = true;
        triggerAudioNotification("danger");
      } else {
        if (offlineBanner) offlineBanner.style.display = "none";
        if (manualButton) manualButton.disabled = false;
        if (manualInput) manualInput.disabled = false;
      }
    }

    window.addEventListener("online", evaluateNetworkConnectivity);
    window.addEventListener("offline", evaluateNetworkConnectivity);
    evaluateNetworkConnectivity();

    // Sound Trigger for Server Flash Messages
    const alertBox = document.getElementById("statusAlertBox");
    if (alertBox) {
      if (alertBox.classList.contains("alert-success")) {
        triggerAudioNotification("success");
      } else if (alertBox.classList.contains("alert-danger")) {
        triggerAudioNotification("danger");
      }
    }

    // Submission Handler Intercepted with Location Verification
    function processKioskSubmission(staffIdValue) {
      if (isSubmissionInProgress) return;

      // Hardware scanners may append Enter, Tab, or other control characters.
      const cleanId = String(staffIdValue || "")
        .replace(/[\r\n\t]+$/g, "")
        .trim();
      if (!cleanId) {
        alert("Please scan a valid badge or type a Staff ID entry manually.");
        return;
      }
      if (cleanId.length > 12) {
        alert("Staff ID must be 12 characters or fewer.");
        return;
      }

      isSubmissionInProgress = true;

      // Verify location before proceeding with form submission
      verifyOnSiteLocation(
        (lat, lng) => {
          const hiddenField = document.getElementById("hidden_staff_id");
          if (hiddenField) hiddenField.value = cleanId;

          // Append latitude and longitude inputs dynamically if not present
          let latInput = document.getElementById("hidden_user_lat");
          let lngInput = document.getElementById("hidden_user_lng");

          if (!latInput) {
            latInput = document.createElement("input");
            latInput.type = "hidden";
            latInput.id = "hidden_user_lat";
            latInput.name = "latitude";
            kioskForm.appendChild(latInput);
          }
          if (!lngInput) {
            lngInput = document.createElement("input");
            lngInput.type = "hidden";
            lngInput.id = "hidden_user_lng";
            lngInput.name = "longitude";
            kioskForm.appendChild(lngInput);
          }

          latInput.value = lat;
          lngInput.value = lng;

          if (isCameraScanning) {
            html5QrCode
              .stop()
              .then(() => {
                isCameraScanning = false;
                kioskForm.submit();
              })
              .catch((err) => {
                console.warn("Camera pipeline closure bypass executed: ", err);
                kioskForm.submit();
              });
          } else {
            kioskForm.submit();
          }
        },
        () => {
          isSubmissionInProgress = false;
          // If location verification fails/denied, resume scanning stream
          if (isCameraScanning && html5QrCode.isPaused) {
            html5QrCode.resume();
          }
        },
      );
    }

    function onScanSuccess(decodedText) {
      processKioskSubmission(decodedText);
    }

    const manualInputField = document.getElementById("manual_staff_id");
    const manualSubmitButton = document.getElementById("manualSubmitBtn");

    if (manualInputField) {
      manualInputField.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
          event.preventDefault();
          processKioskSubmission(manualInputField.value);
        }
      });
    }

    if (manualSubmitButton) {
      manualSubmitButton.addEventListener("click", function () {
        processKioskSubmission(manualInputField.value);
      });
    }

    let scannerBuffer = "";
    let scannerLastKeyTime = 0;
    const scannerKeyInterval = 100;

    document.addEventListener("keydown", function (event) {
      if (event.target === manualInputField || isSubmissionInProgress) return;

      const now = Date.now();
      if (now - scannerLastKeyTime > scannerKeyInterval) {
        scannerBuffer = "";
      }

      if (event.key === "Enter" || event.key === "Tab") {
        if (scannerBuffer) {
          event.preventDefault();
          const scannedId = scannerBuffer;
          scannerBuffer = "";
          scannerLastKeyTime = 0;
          processKioskSubmission(scannedId);
        }
        return;
      }

      if (event.key.length === 1) {
        scannerBuffer += event.key;
        scannerLastKeyTime = now;
      }
    });

    // Universal Camera Initialization Engine (Handles Laptops & Mobile)
    function startCameraPipeline() {
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Step 1: Force laptop browser hardware prompt
        navigator.mediaDevices
          .getUserMedia({ video: true })
          .then((stream) => {
            // Release pre-flight stream so html5QrCode gets access
            stream.getTracks().forEach((track) => track.stop());

            // Step 2: Launch scanner
            html5QrCode
              .start({ facingMode: "environment" }, qrConfig, onScanSuccess)
              .catch(() => {
                return html5QrCode.start(
                  { facingMode: "user" },
                  qrConfig,
                  onScanSuccess,
                );
              })
              .then(() => {
                isCameraScanning = true;
              })
              .catch((err) => {
                console.error("Camera start error:", err);
                isCameraScanning = false;
                showCameraFallback(
                  "📷 Unable to start camera hardware. Use manual entry below.",
                );
              });
          })
          .catch((err) => {
            console.error("MediaDevices permission error:", err);
            isCameraScanning = false;
            showCameraFallback(
              "📷 Camera access denied or blocked by browser settings. Use manual entry below.",
            );
          });
      } else {
        showCameraFallback(
          "📷 Web Camera API is not supported in this browser.",
        );
      }
    }

    function showCameraFallback(messageText) {
      const readerDiv = document.getElementById("reader");
      if (readerDiv) {
        readerDiv.innerHTML =
          `<div style="padding:20px; color:#721c24; background:#f8d7da; font-size:0.85rem;">` +
          `${messageText}` +
          `</div>`;
      }
    }

    if (alertBox) {
      setTimeout(() => {
        startCameraPipeline();
      }, 4000);
    } else {
      startCameraPipeline();
    }
  }
});
