{{--
    Global confirmation modal.

    Usage: add data-confirm="Message shown to the user" to any form (or to a
    link/button with data-href) instead of the native browser confirm().
    Optional attributes:
        data-confirm-title  — modal heading (default: "Please confirm")
        data-confirm-ok     — confirm button label (default: "Confirm")
        data-confirm-class  — confirm button style (default: btn-danger)
--}}
<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-labelledby="appConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold" id="appConfirmTitle">Please confirm</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small" id="appConfirmMessage">Are you sure?</div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" id="appConfirmOk">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var modalEl = document.getElementById('appConfirmModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var okBtn = document.getElementById('appConfirmOk');
    var pending = null; // { action: 'submit'|'navigate', form|href }

    function open(message, title, okLabel, okClass) {
        document.getElementById('appConfirmMessage').textContent = message;
        document.getElementById('appConfirmTitle').textContent = title || 'Please confirm';
        okBtn.textContent = okLabel || 'Confirm';
        okBtn.className = 'btn btn-sm ' + (okClass || 'btn-danger');
        modal.show();
    }

    // Forms opting in through data-confirm.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirmed === '1') return;
        var message = form.getAttribute('data-confirm');
        if (!message) return;

        e.preventDefault();
        e.stopPropagation();
        pending = { action: 'submit', form: form };
        open(
            message,
            form.getAttribute('data-confirm-title'),
            form.getAttribute('data-confirm-ok'),
            form.getAttribute('data-confirm-class')
        );
    }, true);

    // Links/buttons opting in through data-confirm + data-href or form target.
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-confirm][data-href]');
        if (!trigger) return;
        e.preventDefault();
        pending = { action: 'navigate', href: trigger.getAttribute('data-href') };
        open(
            trigger.getAttribute('data-confirm'),
            trigger.getAttribute('data-confirm-title'),
            trigger.getAttribute('data-confirm-ok'),
            trigger.getAttribute('data-confirm-class')
        );
    }, true);

    okBtn.addEventListener('click', function () {
        if (!pending) return;
        var target = pending;
        pending = null;
        modal.hide();
        if (target.action === 'submit') {
            var form = target.form;
            form.dataset.confirmed = '1';
            // requestSubmit() re-fires a real submit event so other gates
            // (e.g. the password-confirmation modal) still run; the flag above
            // stops this handler from prompting a second time. The flag is kept
            // for the lifetime of the page so a retry after a validation error
            // does not prompt again.
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        } else {
            window.location.href = target.href;
        }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        pending = null;
    });
})();
</script>
