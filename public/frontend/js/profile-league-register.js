/* global Stripe */
(function ($) {
  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function renderResponse($box, type, message) {
    if (!$box || !$box.length) return;
    if (!message) {
      $box.empty();
      return;
    }
    var bg = type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-red-50 border-red-200 text-red-900';
    $box.html('<div class="rounded-lg border px-3 py-2 ' + bg + '">' + escapeHtml(message) + '</div>');
  }

  function setTabUI(which) {
    var greenSingles = '#66A157';
    var greenDoubles = '#5FA252';
    var isDoubles = which === 'doubles';
    $('#profile-tab-singles').toggleClass('border-transparent text-white shadow-sm', !isDoubles)
      .toggleClass('border-[#E0E0E0] bg-white text-[#424242]', isDoubles)
      .css('backgroundColor', !isDoubles ? greenSingles : '');
    $('#profile-tab-doubles').toggleClass('border-transparent text-white shadow-sm', isDoubles)
      .toggleClass('border-[#E0E0E0] bg-white text-[#424242]', !isDoubles)
      .css('backgroundColor', isDoubles ? greenDoubles : '');
    $('#profile-singles-league-form').toggleClass('hidden', isDoubles);
    $('#profile-doubles-league-form').toggleClass('hidden', !isDoubles);
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function initPartnerEmailLookup($form) {
    var lookupUrl = $form.data('partner-lookup-url') || '';
    if (!lookupUrl || $form.data('registration-tab') !== 'doubles') return;

    var $email = $form.find('input[name="d2_email"]');
    var $status = $form.find('.partner-email-lookup-status');
    var $fields = $form.find('.partner-detail-field');
    var playerEmail = String($form.data('player-email') || '').toLowerCase();
    var lookupTimer = null;
    var lastLookupEmail = '';

    function setStatus(type, message) {
      if (!$status.length) return;
      if (!message) {
        $status.text('').addClass('hidden').removeClass('text-emerald-700 text-amber-700 text-red-600');
        return;
      }
      var color =
        type === 'success'
          ? 'text-emerald-700'
          : type === 'info'
            ? 'text-[#1d4ed8]'
            : type === 'warn'
              ? 'text-amber-700'
              : 'text-red-600';
      $status
        .text(message)
        .removeClass('hidden text-emerald-700 text-amber-700 text-red-600 text-[#1d4ed8]')
        .addClass(color);
    }

    function clearPartnerFields() {
      $fields.val('');
    }

    function lookupPartner() {
      var email = $.trim($email.val() || '').toLowerCase();
      if (!email) {
        lastLookupEmail = '';
        setStatus('', '');
        return;
      }
      if (!isValidEmail(email)) {
        setStatus('error', 'Enter a valid email address.');
        return;
      }
      if (email === playerEmail) {
        setStatus('error', 'Second player email must be different from your email.');
        return;
      }
      if (email === lastLookupEmail) return;

      lastLookupEmail = email;
      setStatus('', 'Looking up player...');
      $status.removeClass('hidden').addClass('text-[#666666]');

      $.ajax({
        type: 'GET',
        url: lookupUrl,
        data: { email: email },
        dataType: 'json',
        headers: { Accept: 'application/json' },
      })
        .done(function (res) {
          if (res.found) {
            $form.find('[name="d2_first"]').val(res.first_name || '');
            $form.find('[name="d2_last"]').val(res.last_name || '');
            $form.find('[name="d2_phone"]').val(res.phone || '');
            setStatus('info', res.message || 'Tell your buddy to please log in with their account and see match details there.');
            return;
          }
          clearPartnerFields();
          setStatus(
            'info',
            res.message || 'Please tell your buddy to log in with this email after you complete registration.',
          );
        })
        .fail(function (jqXHR) {
          lastLookupEmail = '';
          var msg = 'Unable to look up this email.';
          if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
            msg = jqXHR.responseJSON.message;
          }
          setStatus('error', msg);
        });
    }

    $email.on('blur', lookupPartner);
    $email.on('input', function () {
      var email = $.trim($email.val() || '').toLowerCase();
      if (email !== lastLookupEmail) {
        lastLookupEmail = '';
        setStatus('', '');
        clearPartnerFields();
      }
      window.clearTimeout(lookupTimer);
      lookupTimer = window.setTimeout(function () {
        if (isValidEmail(email) && email !== playerEmail) lookupPartner();
      }, 500);
    });

    if ($.trim($email.val() || '')) {
      lookupPartner();
    }
  }

  function closedDivisionSet() {
    var raw = $('#profile-league-registration').data('closed-divisions');
    if (!raw) return {};
    if (typeof raw === 'string') {
      try {
        raw = JSON.parse(raw);
      } catch (e) {
        return {};
      }
    }
    var set = {};
    if (Array.isArray(raw)) {
      raw.forEach(function (key) {
        set[key] = true;
      });
    }
    return set;
  }

  function isDivisionClosed(leagueId, tab, skill) {
    if (!leagueId || !skill) return false;
    var key = leagueId + ':' + tab + ':' + skill;
    return !!closedDivisionSet()[key];
  }

  function initProfileLeagueForm(formSelector) {
    var $form = $(formSelector);
    if (!$form.length) return;

    var tab = $form.data('registration-tab');
    var $responseBox = $form.find('.profile_league_form_res');
    var $loader = $form.find('.profile-league-loader');
    var $btn = $form.find('.profile-league-submit');

    var stripeKey = $form.data('stripe-key') || '';
    var paymentIntentUrl = $form.data('payment-intent-url') || '';
    var registerUrl = $form.data('register-url') || '';
    var csrf = $form.data('csrf') || $('meta[name="csrf-token"]').attr('content') || '';

    var stripe = stripeKey && window.Stripe ? Stripe(stripeKey) : null;
    var elements = stripe ? stripe.elements() : null;
    var card = null;
    var cardComplete = false;

    function setCardError(message) {
      var $err = $form.find('.stripe-card-error');
      if (!$err.length) return;
      if (!message) {
        $err.text('').addClass('hidden');
        return;
      }
      $err.text(message).removeClass('hidden');
    }

    function mountCard() {
      if (!stripe || !elements || card) return;
      var mount = $form.find('.stripe-card-element').get(0);
      if (!mount) return;
      card = elements.create('card', {
        hidePostalCode: true,
        style: {
          base: {
            color: '#111827',
            fontFamily: 'Montserrat, system-ui, sans-serif',
            fontSize: '14px',
            '::placeholder': { color: '#9CA3AF' },
          },
          invalid: { color: '#DC2626' },
        },
      });
      card.mount(mount);
      card.on('change', function (event) {
        cardComplete = !!event.complete;
        if (event.error && event.error.message) setCardError(event.error.message);
        else setCardError('');
      });
    }

    $form.on('input', 'input[name="d2_phone"]', function () {
      var v = String($(this).val() || '');
      var cleaned = v.replace(/\D+/g, '');
      if (cleaned !== v) $(this).val(cleaned);
    });

    mountCard();
    initPartnerEmailLookup($form);

    $form.on('submit', function (e) {
      e.preventDefault();
      renderResponse($responseBox, '', '');

      var skillName = tab === 'singles' ? 'skill_singles' : 'skill_doubles';
      var tournamentName = tab === 'singles' ? 'tournament_singles' : 'tournament_doubles';
      var skill = $.trim($form.find('[name="' + skillName + '"]').val() || '');
      var leagueId = $.trim($form.find('[name="' + tournamentName + '"]').val() || '');

      $form.find('select, input').removeClass('border-red-500');
      if (!skill) {
        $form.find('[name="' + skillName + '"]').addClass('border-red-500');
        renderResponse($responseBox, 'error', 'Please select a skill level.');
        return;
      }
      if (!leagueId) {
        $form.find('[name="' + tournamentName + '"]').addClass('border-red-500');
        renderResponse($responseBox, 'error', 'Please select a tournament.');
        return;
      }
      if (isDivisionClosed(leagueId, tab, skill)) {
        renderResponse($responseBox, 'error', 'This group has started. Registration is closed for this skill level.');
        return;
      }

      if (tab === 'doubles') {
        var required = ['d2_first', 'd2_last', 'd2_email', 'd2_phone'];
        var missing = false;
        required.forEach(function (name) {
          if (!$.trim($form.find('[name="' + name + '"]').val() || '')) {
            $form.find('[name="' + name + '"]').addClass('border-red-500');
            missing = true;
          }
        });
        if (missing) {
          renderResponse($responseBox, 'error', 'Please complete second player details.');
          return;
        }
      }

      if (!stripe || !elements) {
        setCardError('Payment is unavailable. Please refresh the page.');
        return;
      }
      mountCard();
      if (!cardComplete) {
        setCardError('Card details are required.');
        return;
      }

      $btn.prop('disabled', true);
      if ($loader.length) $loader.removeClass('hidden');

      var email = $form.data('player-email') || '';

      $.ajax({
        type: 'POST',
        url: paymentIntentUrl,
        contentType: 'application/json',
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        data: JSON.stringify({
          league_id: leagueId,
          registration_tab: tab,
          skill_level: skill,
          email: email,
        }),
      })
        .then(function (pi) {
          return stripe.confirmCardPayment(pi.client_secret, {
            payment_method: {
              card: card,
              billing_details: { email: email, name: $form.data('player-name') || undefined },
            },
          });
        })
        .then(function (result) {
          if (result.error) throw new Error(result.error.message || 'Payment failed.');
          if (!result.paymentIntent || result.paymentIntent.status !== 'succeeded') {
            throw new Error('Payment not completed.');
          }
          $form.find('.payment_intent_id').val(result.paymentIntent.id);
          return $.ajax({
            type: 'POST',
            url: registerUrl,
            data: $form.serialize(),
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
          });
        })
        .then(function (res) {
          renderResponse($responseBox, 'success', res.message || 'Registration completed.');
          if (res.redirect_url) {
            window.setTimeout(function () {
              window.location.href = res.redirect_url;
            }, 2000);
          }
        })
        .fail(function (jqXHR) {
          var msg = 'Something went wrong.';
          if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
            msg = jqXHR.responseJSON.message;
          }
          setCardError('');
          renderResponse($responseBox, 'error', msg);
        })
        .always(function () {
          $btn.prop('disabled', false);
          if ($loader.length) $loader.addClass('hidden');
        });
    });
  }

  $(function () {
    if (!$('#profile-tab-singles').length) return;
    $('#profile-tab-singles').on('click', function () { setTabUI('singles'); });
    $('#profile-tab-doubles').on('click', function () { setTabUI('doubles'); });
    var initial = $('#profile-league-registration').data('initial-tab') || 'singles';
    setTabUI(initial);
    initProfileLeagueForm('#profile-singles-league-form');
    initProfileLeagueForm('#profile-doubles-league-form');
  });
})(window.jQuery);
