/**
 * ACMS Core Application Script
 * Externalized event listeners & kiosk scanner logic
 */

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
       2. DOUBLE-SIDED ID CARD PRINTING
       ========================================================================== */
  const printBtn = document.getElementById("printBadgeBtn");
  if (printBtn) {
    printBtn.addEventListener("click", (e) => {
      e.preventDefault();
      window.print();
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
    let isCameraScanning = false; // State tracker to prevent crashing when camera is inactive

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

    // Auto-refresh timer exactly at Noon
    const rightNow = new Date();
    const targetDeadline = new Date();
    targetDeadline.setHours(12, 0, 0, 0);

    if (rightNow < targetDeadline) {
      const timeRemainingDifference =
        targetDeadline.getTime() - rightNow.getTime();
      setTimeout(() => {
        window.location.reload();
      }, timeRemainingDifference);
    }

    // Submission Handler
    function processKioskSubmission(staffIdValue) {
      const cleanId = staffIdValue.trim();
      if (!cleanId) {
        alert("Please scan a valid badge or type a Staff ID entry manually.");
        return;
      }

      const hiddenField = document.getElementById("hidden_staff_id");
      if (hiddenField) hiddenField.value = cleanId;

      // Only stop camera if it was actually running; otherwise submit immediately
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

    // Initialize Camera Pipeline with fallbacks
    // Universal Camera Pipeline (Works on Laptops, Desktops, & Mobile)
    function startCameraPipeline() {
      Html5Qrcode.getCameras()
        .then((devices) => {
          if (devices && devices.length > 0) {
            // Grab the first available camera device (Built-in Webcam / Phone Camera)
            const cameraId = devices[0].id;

            html5QrCode
              .start(cameraId, qrConfig, onScanSuccess)
              .then(() => {
                isCameraScanning = true;
              })
              .catch((err) => {
                console.error("Camera start error:", err);
                isCameraScanning = false;
                showCameraFallback(
                  "📷 Unable to initialize camera hardware. Use manual entry below.",
                );
              });
          } else {
            showCameraFallback(
              "📷 No webcam or camera device found on this system.",
            );
          }
        })
        .catch((err) => {
          console.warn(
            "Device enumeration restricted, trying fallback constraint...",
            err,
          );
          // Fallback attempt if browser restricts device enumeration before prompt
          html5QrCode
            .start({ facingMode: "user" }, qrConfig, onScanSuccess)
            .then(() => {
              isCameraScanning = true;
            })
            .catch((fallbackErr) => {
              isCameraScanning = false;
              console.error("Camera authorization failed: ", fallbackErr);
              showCameraFallback(
                "📷 Camera access denied or blocked by browser. Use manual entry below.",
              );
            });
        });
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
