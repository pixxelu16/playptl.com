(function () {
  'use strict';

  var page = window.charityCausePage;
  if (!page || !page.contributeUrl) return;

  var panel = document.getElementById('charity-contribute-panel');
  var form = document.getElementById('charity-contribute-form');
  var typeInput = document.getElementById('charity-contribute-type');
  var titleEl = document.getElementById('charity-contribute-title');
  var helpEl = document.getElementById('charity-contribute-help');
  var alertEl = document.getElementById('charity-contribute-alert');
  var materialField = document.getElementById('charity-material-field');
  var materialDetail = document.getElementById('charity-contribute-material-detail');
  var quantityField = document.getElementById('charity-quantity-field');
  var quantityInput = document.getElementById('charity-contribute-quantity');
  var quantityLabel = document.getElementById('charity-contribute-quantity-label');
  var moneyFields = document.getElementById('charity-money-fields');
  var amountInput = document.getElementById('charity-contribute-amount');
  var cardMount = document.getElementById('charity-contribute-card-element');
  var cardError = document.getElementById('charity-contribute-card-error');
  var loader = document.getElementById('charity-contribute-loader');
  var submitBtn = document.getElementById('charity-contribute-submit');
  var csrf = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrf ? csrf.getAttribute('content') : '';

  var stripe = page.stripeKey && window.Stripe ? Stripe(page.stripeKey, { advancedFraudSignals: false }) : null;
  var elements = stripe ? stripe.elements({ wallets: { applePay: 'never', googlePay: 'never' } }) : null;
  var card = null;
  var cardComplete = false;
  var currentType = '';

  function parseMoney(str) {
    var n = parseFloat(String(str).replace(/[^0-9.]/g, ''), 10);
    return isNaN(n) ? null : n;
  }

  function formatMoney(amount) {
    var rounded = Math.round(amount * 100) / 100;
    return rounded % 1 === 0 ? String(rounded) : rounded.toFixed(2);
  }

  function showAlert(message, isError) {
    if (!alertEl) return;
    if (!message) {
      alertEl.hidden = true;
      alertEl.textContent = '';
      return;
    }
    alertEl.hidden = false;
    alertEl.textContent = message;
    alertEl.className =
      'mt-4 rounded-lg border px-4 py-3 text-[13px] font-semibold ' +
      (isError ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-800');
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

  function updateMoneySubmitLabel() {
    if (!submitBtn || currentType !== 'money' || !amountInput) return;
    var amount = parseMoney(amountInput.value);
    submitBtn.textContent = amount != null && amount > 0 ? 'Donate $' + formatMoney(amount) : 'Donate Now';
  }

  function setActiveTypeButton(type) {
    document.querySelectorAll('[data-charity-type]').forEach(function (btn) {
      var active = btn.getAttribute('data-charity-type') === type;
      btn.classList.toggle('border-[#60a04b]', active);
      btn.classList.toggle('ring-2', active);
      btn.classList.toggle('ring-[#60a04b]/20', active);
    });
  }

  function configureFields(type) {
    currentType = type;

    if (type === 'material') {
      titleEl.textContent = 'Material Donation';
      helpEl.textContent = 'Tell us what you would like to donate and how much you can provide.';
      materialField.classList.remove('hidden');
      quantityField.classList.remove('hidden');
      moneyFields.classList.add('hidden');
      if (materialDetail) materialDetail.required = true;
      if (quantityInput) quantityInput.required = true;
      quantityLabel.textContent = 'How much material will you donate? *';
      submitBtn.textContent = 'Submit Material Donation';
      return;
    }

    if (type === 'person') {
      titleEl.textContent = 'Volunteer Contribution';
      helpEl.textContent = 'Let us know how many people from your side can volunteer for this cause.';
      materialField.classList.add('hidden');
      quantityField.classList.remove('hidden');
      moneyFields.classList.add('hidden');
      if (materialDetail) {
        materialDetail.required = false;
        materialDetail.value = '';
      }
      if (quantityInput) quantityInput.required = true;
      quantityLabel.textContent = 'How many persons can volunteer? *';
      submitBtn.textContent = 'Submit Volunteer Request';
      return;
    }

    titleEl.textContent = 'Monetary Donation';
    helpEl.textContent = 'Enter your details and card information to donate securely to this cause.';
    materialField.classList.add('hidden');
    quantityField.classList.add('hidden');
    moneyFields.classList.remove('hidden');
    if (materialDetail) {
      materialDetail.required = false;
      materialDetail.value = '';
    }
    if (quantityInput) quantityInput.required = false;
    mountCard();
    updateMoneySubmitLabel();
  }

  function openType(type) {
    showAlert('');
    setCardError('');

    if (!panel || !form || !typeInput) return;

    panel.classList.remove('hidden');
    setActiveTypeButton(type);
    form.reset();
    typeInput.value = type;
    configureFields(type);

    if (type === 'money' && amountInput) {
      amountInput.value = '25';
      updateMoneySubmitLabel();
    }

    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
        if (!response.ok) throw new Error(body.message || 'Something went wrong.');
        return body;
      });
    });
  }

  function collectMoneyPayload() {
    return {
      amount: parseMoney(amountInput ? amountInput.value : ''),
      charity_cause_id: page.causeId,
      donor_name: document.getElementById('charity-contribute-name').value,
      email: document.getElementById('charity-contribute-email').value,
      address: document.getElementById('charity-contribute-address').value,
      city: document.getElementById('charity-contribute-city').value,
      state: document.getElementById('charity-contribute-state').value,
      zip: document.getElementById('charity-contribute-zip').value,
    };
  }

  function validateMoneyPayload(payload) {
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

  function submitMaterialOrPerson() {
    var payload = {
      donation_type: typeInput.value,
      donor_name: document.getElementById('charity-contribute-name').value,
      email: document.getElementById('charity-contribute-email').value,
      phone: document.getElementById('charity-contribute-phone').value,
      quantity: quantityInput ? quantityInput.value : '',
      material_detail: materialDetail ? materialDetail.value : '',
    };

    submitBtn.disabled = true;

    postJson(page.contributeUrl, payload)
      .then(function (body) {
        showAlert(body.message || 'Thank you!', false);
        form.reset();
        typeInput.value = payload.donation_type;
        window.setTimeout(function () {
          window.location.reload();
        }, 1200);
      })
      .catch(function (err) {
        showAlert(err.message || 'Something went wrong.', true);
      })
      .finally(function () {
        submitBtn.disabled = false;
      });
  }

  function submitMoney() {
    if (!stripe || !elements) {
      showAlert('Payment is unavailable. Please refresh the page.', true);
      return;
    }

    mountCard();
    var payload = collectMoneyPayload();
    var validationError = validateMoneyPayload(payload);
    if (validationError) {
      showAlert(validationError, true);
      return;
    }

    if (!cardComplete) {
      setCardError('Card details are required.');
      return;
    }

    setLoading(true);

    postJson(page.paymentIntentUrl, payload)
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
        return postJson(page.storeUrl, payload);
      })
      .then(function (body) {
        showAlert(body.message || 'Thank you for your donation!', false);
        window.setTimeout(function () {
          window.location.reload();
        }, 1200);
      })
      .catch(function (err) {
        showAlert(err.message || 'Something went wrong.', true);
        setLoading(false);
      });
  }

  document.querySelectorAll('[data-charity-type]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openType(btn.getAttribute('data-charity-type'));
    });
  });

  if (amountInput) {
    amountInput.addEventListener('input', updateMoneySubmitLabel);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      showAlert('');
      setCardError('');

      if (typeInput.value === 'money') {
        submitMoney();
        return;
      }

      submitMaterialOrPerson();
    });
  }
})();
