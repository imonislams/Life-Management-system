@php
/**
* Global toast container.
*
* Renders every flash message the current request produced as a toast.
* Works with BOTH conventions used in this project:
*
* ->with('status', 'Saved successfully.') (existing convention)
* session()->flash('success', 'Saved successfully.');
* session()->flash('error', 'Something went wrong.');
* session()->flash('warning', 'Please review this.');
* session()->flash('info', 'Processing…');
*
* The messages are emitted as data attributes and rendered by JS
* (resources/js/alerts.js) so toasts animate, auto-dismiss and stack.
* Server-side rendering of the container keeps it available without JS too.
*/

$toasts = [];

// The project's original convention: a single "status" flash = success.
if (session('status')) {
$toasts[] = ['type' => 'success', 'message' => session('status')];
}

foreach (['success', 'error', 'warning', 'info'] as $type) {
if (session($type)) {
$toasts[] = ['type' => $type, 'message' => session($type)];
}
}

// A generic validation toast. Field-level errors remain visible in the form
// (this never replaces them) — it only adds a friendly summary.
if ($errors->any() && ! in_array('error', array_column($toasts, 'type'), true)) {
$toasts[] = ['type' => 'error', 'message' => 'Please correct the highlighted fields and try again.'];
}
@endphp

<div
    id="toastContainer"
    class="toast-container"
    role="region"
    aria-label="Notifications"
    aria-live="polite"
    aria-atomic="false"
    data-toasts='@json($toasts)'></div>