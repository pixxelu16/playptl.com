(function () {
    var modal = document.getElementById('admin-confirm-modal');
    if (!modal) {
        return;
    }

    var titleEl = document.getElementById('admin-confirm-title');
    var messageEl = document.getElementById('admin-confirm-message');
    var confirmBtn = document.getElementById('admin-confirm-ok');
    var pendingForm = null;

    function openModal(form) {
        pendingForm = form;
        var title = form.getAttribute('data-admin-confirm-title') || 'Are you sure?';
        var message = form.getAttribute('data-admin-confirm-message') || '';
        var confirmLabel = form.getAttribute('data-admin-confirm-button') || 'Confirm';

        if (titleEl) {
            titleEl.textContent = title;
        }
        if (messageEl) {
            messageEl.textContent = message;
            messageEl.hidden = !message;
        }
        if (confirmBtn) {
            confirmBtn.textContent = confirmLabel;
        }

        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('admin-modal-open');
        confirmBtn?.focus();
    }

    function closeModal() {
        modal.hidden = true;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('admin-modal-open');
        pendingForm = null;
    }

    document.querySelectorAll('form[data-admin-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.getAttribute('data-admin-confirm-bypass') === '1') {
                form.removeAttribute('data-admin-confirm-bypass');
                return;
            }
            e.preventDefault();
            openModal(form);
        });
    });

    confirmBtn?.addEventListener('click', function () {
        if (!pendingForm) {
            return;
        }
        var formToSubmit = pendingForm;
        closeModal();
        if (window.AdminFormSubmitLock) {
            window.AdminFormSubmitLock.lockForm(formToSubmit);
        }
        // Native submit skips submit listeners (avoids lock-before-submit race).
        formToSubmit.submit();
    });

    document.querySelectorAll('[data-admin-confirm-cancel]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
