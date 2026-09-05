/**
 * EduManage Core System Client-Side Interaction Logic Engine
 * Handles Navigation Mechanics, Automated Alert Expiration, and Form Validations
 */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // 1. Initialize Layout Viewport Components
    const wrapper = document.getElementById("wrapper");
    const sidebarToggle = document.getElementById("menu-toggle");

    // Implement a layout configuration listener for the sidebar toggle action link
    if (sidebarToggle) {
        sidebarToggle.addEventListener("click", function (event) {
            event.preventDefault();
            wrapper.classList.toggle("toggled");
        });
    }

    // 2. Automated Alert & Toast Notification Decay Engine
    // Automatically fades and removes success status messages after 4.5 seconds
    const statusAlerts = document.querySelectorAll(".alert-dismissible");
    statusAlerts.forEach(function (alert) {
        setTimeout(function () {
            // Check if Bootstrap's built-in alert close routine is available
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                // Native fallback transformation loop if instantiation hasn't finalized
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }
        }, 4500);
    });

    // 3. Client-Side Form Validation Listener Setup
    // Intercepts submission loops to enforce field formats before database delivery
    const transactionalForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(transactionalForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add("was-validated");
        }, false);
    });

    // 4. Global Action Confirmation Helper Utility
    // Attaches programmatic check flags to critical action fields
    const dangerousActions = document.querySelectorAll(".confirm-action");
    dangerousActions.forEach(function (element) {
        element.addEventListener("click", function (event) {
            const warningPrompt = this.getAttribute("data-confirm-text") || "Are you sure you want to proceed?";
            if (!confirm(warningPrompt)) {
                event.preventDefault();
            }
        });
    });
});