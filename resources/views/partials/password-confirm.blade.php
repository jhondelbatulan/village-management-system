{{--
    Password confirmation modal for sensitive actions.

    Any form or link carrying data-password-confirm is intercepted: the user
    must re-enter their password here before the action is allowed to run.
--}}
<div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordConfirmTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning text-dark rounded-circle p-2 me-2"><i class="bi bi-shield-lock"></i></span>
                    <h5 class="modal-title fw-bold mb-0" id="passwordConfirmTitle">Confirm your password</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="passwordConfirmClose"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3" id="passwordConfirmMessage">Enter your password to continue with this action.</p>

                <div class="alert alert-danger py-2 small d-none" id="passwordConfirmError" role="alert"></div>

                <label for="passwordConfirmInput" class="form-label fw-semibold small mb-1">Password</label>
                <div class="input-group">
                    <input type="password"
                           class="form-control form-control-lg"
                           id="passwordConfirmInput"
                           placeholder="Enter your password"
                           autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="passwordConfirmToggle" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="passwordConfirmSubmit">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="passwordConfirmSpinner" role="status"></span>
                    <i class="bi bi-unlock me-1" id="passwordConfirmIcon"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modalEl = document.getElementById('passwordConfirmModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var input = document.getElementById('passwordConfirmInput');
    var errorBox = document.getElementById('passwordConfirmError');
    var submitBtn = document.getElementById('passwordConfirmSubmit');
    var spinner = document.getElementById('passwordConfirmSpinner');
    var icon = document.getElementById('passwordConfirmIcon');
    var message = document.getElementById('passwordConfirmMessage');
    var pendingForm = null;
    var pendingLink = null;

    function resetModal() {
        input.value = '';
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        submitBtn.disabled = false;
        pendingForm = null;
        pendingLink = null;
    }

    function showError(text) {
        errorBox.textContent = text;
        errorBox.classList.remove('d-none');
    }

    function openModal(contextMessage) {
        message.textContent = contextMessage || 'Enter your password to continue with this action.';
        errorBox.classList.add('d-none');
        modal.show();
        setTimeout(function () { input.focus(); }, 350);
    }

    modalEl.addEventListener('hidden.bs.modal', resetModal);

    // Gated forms: ask for the password, then resubmit the form for real.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-password-confirm')) return;

        // An earlier handler (e.g. the onsubmit "are you sure?" confirm) already
        // canceled the submission — respect that and stay out of the way.
        if (e.defaultPrevented) return;

        if (form.getAttribute('data-password-confirmed') === '1') {
            form.removeAttribute('data-password-confirmed');
            return; // confirmation already done — let the submit through
        }

        e.preventDefault();
        pendingForm = form;
        pendingLink = null;
        openModal(form.getAttribute('data-password-confirm-message'));
    });

    // Gated links (report downloads): ask for the password, then follow the URL.
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-password-confirm]');
        if (!link) return;

        e.preventDefault();
        pendingLink = link;
        pendingForm = null;
        openModal(link.getAttribute('data-password-confirm-message'));
    });

    function confirmPassword() {
        var password = input.value;

        if (!password) {
            showError('Please enter your password.');
            input.focus();
            return;
        }

        errorBox.classList.add('d-none');
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        submitBtn.disabled = true;

        fetch('{{ route('password.confirm.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ password: password })
        }).then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        }).then(function (result) {
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            submitBtn.disabled = false;

            if (!result.ok) {
                var message = result.data.message
                    || (result.data.errors && result.data.errors.password && result.data.errors.password[0])
                    || 'The password you entered is incorrect.';
                showError(message);
                input.select();
                return;
            }

            modal.hide();

            if (pendingForm) {
                var form = pendingForm;
                pendingForm = null;
                form.setAttribute('data-password-confirmed', '1');
                HTMLFormElement.prototype.submit.call(form);
            } else if (pendingLink) {
                var link = pendingLink;
                pendingLink = null;
                window.location.href = link.href;
            }
        }).catch(function () {
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            submitBtn.disabled = false;
            showError('Something went wrong. Please try again.');
        });
    }

    submitBtn.addEventListener('click', confirmPassword);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmPassword();
        }
    });
    document.getElementById('passwordConfirmToggle').addEventListener('click', function () {
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        this.querySelector('i').className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
})();
</script>
