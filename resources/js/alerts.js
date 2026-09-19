/**
 * Global alert system: toasts + confirmation modal.
 *
 * Vanilla JavaScript (no framework) to match the rest of this project, which
 * uses a hand-written CSS design system rather than Tailwind/Alpine.
 *
 * See docs/UI/ALERTS_AND_NOTIFICATIONS.md for the full usage guide.
 */

(function () {
    "use strict";

    // ---------------------------------------------------------------------
    // Configuration — change timings/limits here (centralized).
    // ---------------------------------------------------------------------
    const CONFIG = {
        // Auto-dismiss duration per type (ms). null = stay until dismissed.
        durations: {
            success: 4000,
            info: 5000,
            warning: 6000,
            error: 7000,
        },
        // Maximum toasts visible at once; the oldest is removed beyond this.
        maxToasts: 4,
        // Whether clicking the page background closes the confirm dialog.
        closeConfirmOnBackdrop: true,
    };

    const ICONS = {
        success: "\u2713", // ✓
        error: "!",
        warning: "\u26A0", // ⚠
        info: "i",
    };

    const TYPE_LABELS = {
        success: "Success",
        error: "Error",
        warning: "Warning",
        info: "Information",
    };

    // ---------------------------------------------------------------------
    // Toasts
    // ---------------------------------------------------------------------

    function toastContainer() {
        return document.getElementById("toastContainer");
    }

    /**
     * Show a toast. `type` ∈ success | error | warning | info.
     * @param {string} message
     * @param {string} type
     * @param {number|null} duration  override; null disables auto-dismiss
     */
    function showToast(message, type, duration) {
        const container = toastContainer();
        if (!container || !message) {
            return null;
        }

        type = ICONS[type] ? type : "info";

        // Enforce the visible limit.
        const existing = container.querySelectorAll(".toast");
        if (existing.length >= CONFIG.maxToasts) {
            removeToast(existing[0]);
        }

        const toast = document.createElement("div");
        toast.className = "toast toast-" + type;

        const icon = document.createElement("span");
        icon.className = "toast-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.textContent = ICONS[type];

        const body = document.createElement("div");
        body.className = "toast-body";
        // A visually hidden label gives screen readers the alert type, since
        // the type is otherwise conveyed by icon/colour.
        const srLabel = document.createElement("span");
        srLabel.className = "sr-only";
        srLabel.textContent = TYPE_LABELS[type] + ": ";
        body.appendChild(srLabel);
        body.appendChild(document.createTextNode(message));

        const close = document.createElement("button");
        close.type = "button";
        close.className = "toast-close";
        close.setAttribute("aria-label", "Dismiss notification");
        close.innerHTML = "&times;";
        close.addEventListener("click", function () {
            removeToast(toast);
        });

        toast.setAttribute("role", type === "error" ? "alert" : "status");
        toast.appendChild(icon);
        toast.appendChild(body);
        toast.appendChild(close);
        container.appendChild(toast);

        // Enter animation on the next frame so the transition runs.
        requestAnimationFrame(function () {
            toast.classList.add("toast-visible");
        });

        const ms = duration === undefined ? CONFIG.durations[type] : duration;
        if (ms) {
            const timer = setTimeout(function () {
                removeToast(toast);
            }, ms);
            // Cancel the timer if the user closes it manually first.
            toast._dismissTimer = timer;
        }

        return toast;
    }

    function removeToast(toast) {
        if (!toast || !toast.parentNode) {
            return;
        }
        if (toast._dismissTimer) {
            clearTimeout(toast._dismissTimer);
        }
        toast.classList.add("toast-leaving");
        toast.classList.remove("toast-visible");

        const done = function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        };
        // Fall back to a timeout in case transitionend never fires.
        toast.addEventListener("transitionend", done, { once: true });
        setTimeout(done, 300);
    }

    /**
     * Emit any server-side flash messages serialized into the container.
     */
    function flushServerToasts() {
        const container = toastContainer();
        if (!container) {
            return;
        }

        let toasts = [];
        try {
            toasts = JSON.parse(container.dataset.toasts || "[]");
        } catch (e) {
            toasts = [];
        }
        if (!Array.isArray(toasts)) {
            toasts = [];
        }

        toasts.forEach(function (t, index) {
            // Stagger slightly so stacked toasts appear sequentially.
            setTimeout(function () {
                showToast(t.message || "", t.type || "info");
            }, index * 120);
        });
    }

    // ---------------------------------------------------------------------
    // Confirmation modal
    // ---------------------------------------------------------------------

    let pendingTrigger = null; // the form/button awaiting confirmation
    let lastFocused = null;

    function modalEls() {
        return {
            overlay: document.getElementById("confirmModal"),
            title: document.getElementById("confirmModalTitle"),
            message: document.getElementById("confirmModalMessage"),
            cancel: document.getElementById("confirmModalCancel"),
            confirm: document.getElementById("confirmModalConfirm"),
        };
    }

    function openConfirm(trigger) {
        const els = modalEls();
        if (!els.overlay) {
            // No modal available: fail safe by letting the action proceed.
            submitTrigger(trigger);
            return;
        }

        pendingTrigger = trigger;
        lastFocused = document.activeElement;

        els.title.textContent = trigger.dataset.confirmTitle || "Are you sure?";
        els.message.textContent =
            trigger.dataset.confirmMessage || "This action cannot be undone.";
        els.confirm.textContent = trigger.dataset.confirmAction || "Confirm";

        // Allow a non-destructive variant (e.g. data-confirm-variant="primary").
        const variant = trigger.dataset.confirmVariant || "danger";
        els.confirm.className =
            variant === "primary" ? "btn-primary" : "btn-danger";

        els.overlay.hidden = false;
        requestAnimationFrame(function () {
            els.overlay.classList.add("confirm-visible");
        });

        document.body.style.overflow = "hidden";
        els.confirm.focus();
    }

    function closeConfirm() {
        const els = modalEls();
        if (!els.overlay || els.overlay.hidden) {
            return;
        }

        els.overlay.classList.remove("confirm-visible");
        document.body.style.overflow = "";

        const finish = function () {
            if (pendingTrigger !== null) {
                pendingTrigger = null;
            }
        };
        setTimeout(finish, 200);

        // Return focus to the element that opened the dialog (accessibility).
        if (lastFocused && typeof lastFocused.focus === "function") {
            lastFocused.focus();
        }
        lastFocused = null;
        els.overlay.hidden = true;
    }

    function submitTrigger(trigger) {
        if (!trigger) {
            return;
        }

        if (trigger.tagName === "FORM") {
            // Bypass this handler so the confirm step is not re-entered.
            trigger.dataset.confirmed = "true";
            if (typeof trigger.requestSubmit === "function") {
                trigger.requestSubmit();
            } else {
                trigger.submit();
            }
            return;
        }

        // A standalone control (button/a) that points at a form via data-confirm-form,
        // or simply carries its own click behaviour.
        const formId = trigger.dataset.confirmForm;
        if (formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.dataset.confirmed = "true";
                if (typeof form.requestSubmit === "function") {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
                return;
            }
        }

        // Fallback: dispatch a normal click with the confirmation bypassed.
        trigger.dataset.confirmed = "true";
        trigger.click();
    }

    function initConfirm() {
        const els = modalEls();
        if (!els.overlay) {
            return;
        }

        // Intercept every element that asks for confirmation.
        document.addEventListener(
            "submit",
            function (event) {
                const form = event.target;
                // NOTE: use hasAttribute, not dataset truthiness — "data-confirm"
                // is a valueless attribute, so form.dataset.confirm === "" is falsy.
                if (
                    !(form instanceof HTMLFormElement) ||
                    !form.hasAttribute("data-confirm")
                ) {
                    return;
                }
                // Already confirmed once — let it through.
                if (form.dataset.confirmed === "true") {
                    delete form.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                openConfirm(form);
            },
            true,
        );

        // Standalone buttons/links that are NOT inside a form (or that point at one).
        document.addEventListener(
            "click",
            function (event) {
                const trigger = event.target.closest("[data-confirm]");
                if (!trigger || trigger.tagName === "FORM") {
                    return;
                }
                if (trigger.dataset.confirmed === "true") {
                    delete trigger.dataset.confirmed;
                    return;
                }
                event.preventDefault();
                openConfirm(trigger);
            },
            true,
        );

        // Confirm / cancel buttons.
        els.confirm.addEventListener("click", function () {
            const trigger = pendingTrigger;
            closeConfirm();
            submitTrigger(trigger);
        });

        els.cancel.addEventListener("click", function () {
            closeConfirm();
        });

        // Backdrop click closes (optional).
        if (CONFIG.closeConfirmOnBackdrop) {
            els.overlay.addEventListener("mousedown", function (event) {
                if (event.target === els.overlay) {
                    closeConfirm();
                }
            });
        }

        // ESC closes.
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !els.overlay.hidden) {
                closeConfirm();
            }
        });
    }

    // ---------------------------------------------------------------------
    // Init
    // ---------------------------------------------------------------------

    function init() {
        initConfirm();
        flushServerToasts();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    /**
     * Programmatic confirmation for JS-driven actions (not plain forms):
     *
     *   window.Alerts.confirm({ title, message, action }, function () { ... });
     *
     * @param {{title?:string, message?:string, action?:string, variant?:string}} opts
     * @param {Function} onConfirm
     */
    function confirmAction(opts, onConfirm) {
        opts = opts || {};
        const els = modalEls();

        if (!els.overlay) {
            if (typeof onConfirm === "function") {
                onConfirm();
            }
            return;
        }

        lastFocused = document.activeElement;
        els.title.textContent = opts.title || "Are you sure?";
        els.message.textContent = opts.message || "This action cannot be undone.";
        els.confirm.textContent = opts.action || "Confirm";
        els.confirm.className = opts.variant === "primary" ? "btn-primary" : "btn-danger";

        els.overlay.hidden = false;
        requestAnimationFrame(function () {
            els.overlay.classList.add("confirm-visible");
        });
        document.body.style.overflow = "hidden";
        els.confirm.focus();

        const handler = function () {
            els.confirm.removeEventListener("click", handler);
            closeConfirm();
            if (typeof onConfirm === "function") {
                onConfirm();
            }
        };
        els.confirm.addEventListener("click", handler);
    }

    // Public API for programmatic use from page scripts.
    window.Alerts = {
        success: function (msg, ms) {
            return showToast(msg, "success", ms);
        },
        error: function (msg, ms) {
            return showToast(msg, "error", ms);
        },
        warning: function (msg, ms) {
            return showToast(msg, "warning", ms);
        },
        info: function (msg, ms) {
            return showToast(msg, "info", ms);
        },
        confirm: confirmAction,
        config: CONFIG,
    };
})();
