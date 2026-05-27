(function () {
    function submitButtons(form) {
        var list = [];
        form.querySelectorAll('button, input').forEach(function (el) {
            if (el.type === 'submit' || (el.tagName === 'BUTTON' && !el.type)) {
                list.push(el);
            }
        });

        return list;
    }

    function unlockForm(form) {
        if (!form) {
            return;
        }

        form.removeAttribute('data-admin-submit-locked');
        form.classList.remove('is-submitting');

        submitButtons(form).forEach(function (btn) {
            var wasDisabled = btn.getAttribute('data-admin-submit-was-disabled');
            if (wasDisabled !== null) {
                btn.disabled = wasDisabled === '1';
                btn.removeAttribute('data-admin-submit-was-disabled');
            } else {
                btn.disabled = false;
            }
            btn.removeAttribute('aria-busy');

            var label = btn.querySelector('span');
            var savedLabel = btn.getAttribute('data-admin-submit-label');
            if (label && savedLabel) {
                label.textContent = savedLabel;
                label.removeAttribute('data-admin-submit-label');
            } else if (btn.tagName === 'BUTTON' && savedLabel) {
                btn.textContent = savedLabel;
                btn.removeAttribute('data-admin-submit-label');
            } else if (btn.tagName === 'INPUT' && btn.type === 'submit' && savedLabel) {
                btn.value = savedLabel;
                btn.removeAttribute('data-admin-submit-label');
            }
        });
    }

    function lockForm(form) {
        if (!form || form.getAttribute('data-admin-submit-locked') === '1') {
            return;
        }

        form.setAttribute('data-admin-submit-locked', '1');
        form.classList.add('is-submitting');

        submitButtons(form).forEach(function (btn) {
            if (btn.getAttribute('data-admin-submit-was-disabled') === null) {
                btn.setAttribute('data-admin-submit-was-disabled', btn.disabled ? '1' : '0');
            }
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');

            var label = btn.querySelector('span');
            if (label && !label.getAttribute('data-admin-submit-label')) {
                label.setAttribute('data-admin-submit-label', label.textContent);
                label.textContent = 'Please wait…';
            } else if (btn.tagName === 'BUTTON' && !btn.getAttribute('data-admin-submit-label')) {
                btn.setAttribute('data-admin-submit-label', btn.textContent);
                btn.textContent = 'Please wait…';
            } else if (btn.tagName === 'INPUT' && btn.type === 'submit' && !btn.getAttribute('data-admin-submit-label')) {
                btn.setAttribute('data-admin-submit-label', btn.value);
                btn.value = 'Please wait…';
            }
        });
    }

    function lockButton(btn) {
        if (!btn || btn.disabled) {
            return;
        }
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
    }

    window.AdminFormSubmitLock = {
        lockForm: lockForm,
        lockButton: lockButton,
        unlockForm: unlockForm,
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (form.getAttribute('data-admin-confirm-bypass') === '1') {
            return;
        }
        if (form.getAttribute('data-admin-submit-locked') === '1') {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-admin-submit-locked="1"]').forEach(unlockForm);
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (e.defaultPrevented) {
            return;
        }
        if (form.getAttribute('data-no-submit-lock') === '1') {
            return;
        }
        lockForm(form);
    }, false);
})();
