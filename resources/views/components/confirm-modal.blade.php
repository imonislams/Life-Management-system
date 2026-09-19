@php
/**
* Global confirmation modal — a single, reusable dialog for every destructive
* (or otherwise important) action in the application.
*
* Usage (no browser confirm()):
*
* <form method="POST" action="..." data-confirm
    * data-confirm-title="Delete Expense?"
    * data-confirm-message="Are you sure? This action cannot be undone."
    * data-confirm-action="Delete">
    * @csrf
    * @method('DELETE')
    * <button type="submit">Delete</button>
    * </form>
*
* Or on a standalone button:
*
* <button type="button" data-confirm data-confirm-title="..." ...>Delete</button>
*
* The modal is driven by resources/js/alerts.js. It never weakens security:
* the underlying form still submits normally with its CSRF token and HTTP
* method, and server-side authorization/validation are untouched.
*/
@endphp

<div
    id="confirmModal"
    class="confirm-overlay"
    role="presentation"
    hidden>
    <div
        class="confirm-dialog"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="confirmModalTitle"
        aria-describedby="confirmModalMessage">
        <h2 id="confirmModalTitle" class="confirm-title">Are you sure?</h2>
        <p id="confirmModalMessage" class="confirm-message">
            This action cannot be undone.
        </p>

        <div class="confirm-actions">
            <button type="button" id="confirmModalCancel" class="btn-secondary">
                Cancel
            </button>
            <button type="button" id="confirmModalConfirm" class="btn-danger">
                Confirm
            </button>
        </div>
    </div>
</div>