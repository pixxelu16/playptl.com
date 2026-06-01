(function () {
  'use strict';

  var modal = document.getElementById('charity-donate-modal');
  if (!modal) return;

  var form = document.getElementById('charity-donate-form');
  var amountInput = document.getElementById('charity-donate-modal-amount');
  var submitBtn = document.getElementById('charity-donate-modal-submit');
  var alertBox = document.getElementById('charity-donate-modal-alert');
  var cardMount = document.getElementById('charity-donate-card-element');
  var cardError = document.getElementById('charity-donate-card-error');
  var loader = document.getElementById('charity-donate-modal-loader');

  var stripeKey = modal.getAttribute('data-stripe-key') || '';
  var paymentIntentUrl = modal.getAttribute('data-payment-intent-url') || '';
  var storeUrl = modal.getAttribute('data-store-url') || '';
  var csrf = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrf ? csrf.getAttribute('content') : '';

  var stripe = stripeKey && window.Stripe ? Stripe(stripeKey, { advancedFraudSignals: false }) : null;
  var elements = stripe ? stripe.elements({ wallets: { applePay: 'never', googlePay: 'never' } }) : null;
  var card = null;
  var cardComplete = false;

  function parseMoney(str) {
    var n = parseFloat(String(str).replace(/[^0-9.]/g, ''), 10);
    return isNaN(n) ? null : n;
  }

  function formatMoney(amount) {
    var rounded = Math.round(amount * 100) / 100;
    return rounded % 1 === 0 ? String(rounded) : rounded.toFixed(2);
  }

  function hideAlert() {
    if (!alertBox) return;
    alertBox.textContent = '';
    alertBox.hidden = true;
    alertBox.className = 'mb-4 rounded-lg border px-4 py-3 text-[13px] font-semibold';
  }

  function showError(message) {
    if (!alertBox) return;
    if (!message) {
      hideAlert();
      return;
    }
    alertBox.textContent = message;
    alertBox.hidden = false;
    alertBox.className =
      'mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-semibold text-red-700';
  }

  function showSuccess(message) {
    if (!alertBox) return;
    alertBox.textContent = message || 'Thank you for your donation!';
    alertBox.hidden = false;
    alertBox.className =
      'mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-800';
  }

  function setCardError(message) {
    if (!cardError) return;
    if (!message) {
      cardError.textContent = '';
      cardError.hidden = true;
      return;
    }
    cardError.textContent = message;
    cardError.hidden = false;
  }

  function mountCard() {
    if (!stripe || !elements || !cardMount || card) return;
    card = elements.create('card', {
      hidePostalCode: true,
      wallets: { applePay: 'never', googlePay: 'never' },
      style: {
        base: {
          color: '#111827',
          fontFamily: 'Montserrat, Inter, system-ui, sans-serif',
          fontSize: '14px',
          '::placeholder': { color: '#9CA3AF' },
        },
        invalid: { color: '#DC2626' },
      },
    });
    card.mount(cardMount);
    card.on('change', function (event) {
      cardComplete = !!event.complete;
      if (event.error && event.error.message) setCardError(event.error.message);
      else setCardError('');
    });
  }

  function setLoading(isLoading) {
    if (submitBtn) submitBtn.disabled = isLoading;
    if (loader) loader.classList.toggle('hidden', !isLoading);
  }

  function setCauseContext(causeId, causeTitle) {
    var causeInput = document.getElementById('charity-donate-cause-id');
    var subtitle = document.getElementById('charity-donate-modal-subtitle');
    if (causeInput) {
      causeInput.value = causeId != null && causeId !== '' ? String(causeId) : '';
    }
    if (subtitle) {
      subtitle.textContent =
        causeTitle && String(causeTitle).trim() !== ''
          ? 'Supporting: ' + String(causeTitle).trim()
          : 'Your contribution supports our charity programs.';
    }
  }

  function openModal(amount, causeId, causeTitle) {
    hideAlert();
    setCardError('');
    if (form) form.reset();
    if (submitBtn) submitBtn.disabled = false;
    setCauseContext(causeId, causeTitle);
    if (amountInput && amount != null && amount > 0) {
      amountInput.value = formatMoney(amount);
    }
    updateSubmitLabel();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    mountCard();
    var firstField = form ? form.querySelector('input:not([type="hidden"])') : null;
    if (firstField) firstField.focus();
  }

  window.openCharityDonateModal = openModal;

  function closeModal() {
    hideAlert();
    setCardError('');
    if (form) form.reset();
    if (submitBtn) submitBtn.disabled = false;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  }

  function updateSubmitLabel() {
    if (!submitBtn || !amountInput) return;
    var amount = parseMoney(amountInput.value);
    submitBtn.textContent = amount != null && amount > 0 ? 'Donate $' + formatMoney(amount) : 'Donate';
  }

  function getSelectedAmountFromWidget() {
    var other = document.getElementById('charity-donate-other');
    if (other && other.value.trim()) {
      var custom = parseMoney(other.value);
      if (custom != null && custom > 0) return custom;
    }
    var active = document.querySelector('#charity-donate-widget .donate-preset-btn.border-white.bg-white');
    if (active) {
      var preset = parseInt(active.getAttribute('data-donate-preset'), 10);
      if (!isNaN(preset) && preset > 0) return preset;
    }
    return 25;
  }

  function collectPayload() {
    var amount = parseMoney(amountInput ? amountInput.value : '');
    var causeInput = document.getElementById('charity-donate-cause-id');
    var causeId = causeInput && causeInput.value ? parseInt(causeInput.value, 10) : null;
    return {
      amount: amount,
      charity_cause_id: !isNaN(causeId) && causeId > 0 ? causeId : null,
      donor_name: (document.getElementById('charity-donate-name') || {}).value || '',
      email: (document.getElementById('charity-donate-email') || {}).value || '',
      address: (document.getElementById('charity-donate-address') || {}).value || '',
      city: (document.getElementById('charity-donate-city') || {}).value || '',
      state: (document.getElementById('charity-donate-state') || {}).value || '',
      zip: (document.getElementById('charity-donate-zip') || {}).value || '',
    };
  }

  function validatePayload(payload) {
    if (payload.amount == null || payload.amount < 1) return 'Please enter a valid donation amount (minimum $1).';
    if (!payload.donor_name.trim()) return 'Please enter your name.';
    if (!payload.address.trim()) return 'Please enter your address.';
    if (!payload.city.trim()) return 'Please enter your city.';
    if (!payload.state.trim()) return 'Please enter your state.';
    if (payload.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.email.trim())) {
      return 'Please enter a valid email address.';
    }
    return null;
  }

  function postJson(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data),
    }).then(function (response) {
      return response.json().then(function (body) {
        if (!response.ok) {
          var message = body && body.message ? body.message : 'Something went wrong.';
          throw new Error(message);
        }
        return body;
      });
    });
  }

  document.querySelectorAll('[data-open-charity-donate]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var preset = btn.getAttribute('data-donate-amount');
      var amount = preset ? parseFloat(preset) : getSelectedAmountFromWidget();
      openModal(amount);
    });
  });

  modal.querySelectorAll('[data-close-charity-donate]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      closeModal();
    });
  });

  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
  });

  if (amountInput) {
    amountInput.addEventListener('input', updateSubmitLabel);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      hideAlert();

      if (!stripe || !elements) {
        showError('Payment is unavailable. Please refresh the page.');
        return;
      }

      mountCard();
      var payload = collectPayload();
      var validationError = validatePayload(payload);
      if (validationError) {
        showError(validationError);
        return;
      }

      if (!cardComplete) {
        setCardError('Card details are required.');
        return;
      }

      setLoading(true);

      postJson(paymentIntentUrl, payload)
        .then(function (pi) {
          return stripe.confirmCardPayment(pi.client_secret, {
            payment_method: {
              card: card,
              billing_details: {
                name: payload.donor_name.trim(),
                email: payload.email.trim() || undefined,
                address: {
                  line1: payload.address.trim(),
                  city: payload.city.trim(),
                  state: payload.state.trim(),
                  postal_code: payload.zip.trim() || undefined,
                },
              },
            },
          });
        })
        .then(function (result) {
          if (result.error) throw new Error(result.error.message || 'Payment failed.');
          if (!result.paymentIntent || result.paymentIntent.status !== 'succeeded') {
            throw new Error('Payment not completed.');
          }
          payload.payment_intent_id = result.paymentIntent.id;
          return postJson(storeUrl, payload);
        })
        .then(function (body) {
          showSuccess(body.message || 'Thank you for your donation!');
          window.setTimeout(function () {
            window.location.reload();
          }, 1000);
        })
        .catch(function (err) {
          showError(err.message || 'Something went wrong.');
          setLoading(false);
        });
    });
  }
})();
