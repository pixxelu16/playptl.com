/* global Stripe, validate */
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
    $box.html(
      '<div class="rounded-[10px] border px-3 py-2 ' +
        bg +
        '">' +
        escapeHtml(message) +
        '</div>'
    );
  }

  function setTabUI(which) {
    var greenSingles = '#5DA44E';
    var greenDoubles = '#5FA252';

    var $tabSingles = $('#tab-singles');
    var $tabDoubles = $('#tab-doubles');
    var $singlesForm = $('#singles-register-form');
    var $doublesForm = $('#doubles-register-form');

    var isDoubles = which === 'doubles';

    $doublesForm.toggleClass('hidden', !isDoubles);
    $singlesForm.toggleClass('hidden', isDoubles);

    if ($tabDoubles.length) {
      $tabDoubles.css('backgroundColor', isDoubles ? greenDoubles : '#fff');
      $tabDoubles.css('color', isDoubles ? '#fff' : '#222');
      $tabDoubles.css('border', isDoubles ? 'none' : '1px solid #d1d1d1');
    }
    if ($tabSingles.length) {
      $tabSingles.css('backgroundColor', !isDoubles ? greenSingles : '#fff');
      $tabSingles.css('color', !isDoubles ? '#fff' : '#222');
      $tabSingles.css('border', !isDoubles ? 'none' : '1px solid #d1d1d1');
    }
  }

  function closedDivisionSet() {
    var raw = $('#register-league-gate').data('closed-divisions');
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
    return !!closedDivisionSet()[leagueId + ':' + tab + ':' + skill];
  }

  function closedGroupCardSet() {
    var raw = $('#register-league-gate').attr('data-closed-group-cards');
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

  function isGroupCardClosed(leagueId, groupCardId) {
    if (!leagueId || !groupCardId) return false;
    return !!closedGroupCardSet()[leagueId + ':' + groupCardId];
  }

  function leagueFeesMap() {
    var raw = $('#register-league-gate').attr('data-league-fees');
    if (!raw) return { default: { singles: '0.00', doubles: '0.00' } };
    if (typeof raw === 'string') {
      try {
        raw = JSON.parse(raw);
      } catch (e) {
        return { default: { singles: '0.00', doubles: '0.00' } };
      }
    }
    return raw || { default: { singles: '0.00', doubles: '0.00' } };
  }

  function entryFeeForLeague(leagueId, tab) {
    var fees = leagueFeesMap();
    var key = leagueId ? String(leagueId) : '';
    var row = key && fees[key] ? fees[key] : fees.default || { singles: '0.00', doubles: '0.00' };
    return tab === 'doubles' ? row.doubles || '0.00' : row.singles || '0.00';
  }

  function syncRegisterEntryFee($form) {
    if (!$form || !$form.length) return;
    var tab = $form.data('registration-tab') || 'singles';
    var leagueId = $form.find('select[name="tournament_' + tab + '"]').val();
    var amount = entryFeeForLeague(leagueId, tab);
    $form.find('.entry-fee-amount').text(amount);
    $form.data('fee', amount);
  }

  function tournamentGroupsUrl() {
    return $('#register-league-gate').attr('data-tournament-groups-url') || '';
  }

  function registerFormForTab(tab) {
    return tab === 'doubles' ? $('#doubles-register-form') : $('#singles-register-form');
  }

  function loadTournamentGroups(tab) {
    var $form = registerFormForTab(tab);
    if (!$form.length) return;

    var $wrap = $form.find('.tournament-group-wrap[data-tab="' + tab + '"]');
    var $select = $wrap.find('.tournament-group-select');
    var $hint = $wrap.find('.tournament-group-hint');
    var $loading = $wrap.find('.tournament-group-loading');
    var leagueId = $form.find('select[name="tournament_' + tab + '"]').val();
    var url = tournamentGroupsUrl();
    var preserve = $select.data('old') || $select.val() || '';

    if (!leagueId) {
      $wrap.addClass('hidden');
      $select.prop('required', false).html('<option value="">Select group</option>');
      $hint.addClass('hidden');
      $loading.addClass('hidden');
      return;
    }

    $wrap.removeClass('hidden');

    if (!url) {
      $select.prop('required', false).html('<option value="">Select group</option>');
      $hint.removeClass('hidden').text('Groups could not be loaded.');
      $loading.addClass('hidden');
      return;
    }

    $loading.removeClass('hidden');
    $hint.addClass('hidden');

    $.getJSON(url, { league_id: leagueId, tab: tab })
      .done(function (payload) {
        var groups = (payload && payload.groups) || [];
        var html = '<option value="">Select group</option>';
        var openCount = 0;

        groups.forEach(function (g) {
          var label = escapeHtml(g.label || g.name || 'Group');
          if (!g.registration_open && g.closed_reason) {
            label += ' (closed)';
          }
          html +=
            '<option value="' +
            escapeHtml(String(g.id)) +
            '"' +
            (g.registration_open ? '' : ' disabled') +
            '>' +
            label +
            '</option>';
          if (g.registration_open) openCount++;
        });

        $select.html(html);

        if (groups.length === 0) {
          $select.prop('required', false);
          $hint
            .removeClass('hidden')
            .text('No groups available for this tournament yet. Contact the league admin.');
        } else {
          $select.prop('required', true);
          if (preserve) {
            $select.val(String(preserve));
          }
          if (!$select.val() && openCount === 1) {
            $select.find('option:not([disabled]):not([value=""])').first().prop('selected', true);
          }
          $hint
            .removeClass('hidden')
            .text('Subgroup (A, B, C…) is assigned automatically when you register.');
        }
      })
      .fail(function () {
        $select.html('<option value="">Select group</option>').prop('required', false);
        $hint.removeClass('hidden').text('Could not load groups. Please try again.');
      })
      .always(function () {
        $loading.addClass('hidden');
        $select.removeData('old');
      });
  }

  function refreshTournamentGroupsForVisibleTab() {
    var tab = $('#doubles-register-form').hasClass('hidden') ? 'singles' : 'doubles';
    loadTournamentGroups(tab);
  }

  function initRegisterForm(formSelector) {
    var $form = $(formSelector);
    if (!$form.length) return;

    var tab = $form.data('registration-tab');
    var $responseBox = $form.find('.custom_register_form_res');
    var $loader = $form.find('.common-loader');
    var $btn = $form.find('.disable-button');

    var stripeKey = $form.data('stripe-key') || '';
    var paymentIntentUrl = $form.data('payment-intent-url') || '';
    var registerUrl = $form.data('register-url') || $form.attr('action') || '';
    var csrf = $form.data('csrf') || '';

    var stripe = stripeKey && window.Stripe ? Stripe(stripeKey, { advancedFraudSignals: false }) : null;
    var elements = stripe
      ? stripe.elements({
          wallets: { applePay: 'never', googlePay: 'never' },
        })
      : null;
    var card = null;
    var cardComplete = false;
    var pendingSuccessRedirect = false;
    var successRedirectDelayMs = 3000;

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
      if (!stripe || !elements) return;
      if (card) return;
      var mount = $form.find('.stripe-card-element').get(0);
      if (!mount) return;
      card = elements.create('card', {
        hidePostalCode: true,
        wallets: { applePay: 'never', googlePay: 'never' },
        style: {
          base: {
            color: '#111827',
            fontFamily: 'Inter, Montserrat, system-ui, -apple-system, Segoe UI, Roboto, sans-serif',
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
        $(mount).toggleClass('border-red-500', !cardComplete && event.empty === false);
      });
    }

    // Live UX
    $form.on('input change', 'input, select, textarea', function () {
      $(this).removeClass('border-red-500');
    });

    $form.on('input', 'input[name="phone_singles"], input[name="phone_doubles"], input[name="d2_phone"]', function () {
      var v = String($(this).val() || '');
      var cleaned = v.replace(/\D+/g, '');
      if (cleaned !== v) $(this).val(cleaned);
    });

    function clearFieldErrors() {
      $form.find('input,select').removeClass('border-red-500');
    }

    function applyFieldErrors(errors) {
      if (!errors) return;
      Object.keys(errors).forEach(function (name) {
        $form.find('[name="' + name + '"]').addClass('border-red-500');
      });
    }

    function constraintsFor(tabKey) {
      var base = {
        email: { presence: { allowEmpty: false }, email: true },
        password: { presence: { allowEmpty: false }, length: { minimum: 8 } },
        password_confirmation: {
          presence: { allowEmpty: false },
          equality: { attribute: 'password', message: '^Passwords do not match.' },
        },
      };

      if (tabKey === 'singles') {
        return $.extend({}, base, {
          singles_first: { presence: { allowEmpty: false } },
          singles_last: { presence: { allowEmpty: false } },
          phone_singles: { presence: { allowEmpty: false } },
          city_singles: { presence: { allowEmpty: false } },
          state_singles: { presence: { allowEmpty: false } },
          age_group_singles: { presence: { allowEmpty: false } },
          skill_singles: { presence: { allowEmpty: false } },
          sex_singles: { presence: { allowEmpty: false } },
          tournament_singles: { presence: { allowEmpty: false } },
        });
      }

      return $.extend({}, base, {
        d1_first: { presence: { allowEmpty: false } },
        d1_last: { presence: { allowEmpty: false } },
        phone_doubles: { presence: { allowEmpty: false } },
        city_doubles: { presence: { allowEmpty: false } },
        state_doubles: { presence: { allowEmpty: false } },
        age_group_doubles: { presence: { allowEmpty: false } },
        skill_doubles: { presence: { allowEmpty: false } },
        sex_doubles: { presence: { allowEmpty: false } },
        tournament_doubles: { presence: { allowEmpty: false } },
        d2_first: { presence: { allowEmpty: false } },
        d2_last: { presence: { allowEmpty: false } },
        d2_email: { presence: { allowEmpty: false }, email: true },
        d2_phone: { presence: { allowEmpty: false } },
      });
    }

    function validateForm() {
      if (!window.validate) {
        renderResponse($responseBox, 'error', 'Validation library missing. Please refresh.');
        return { _validation: ['missing'] };
      }
      clearFieldErrors();
      var values = {};
      $form.serializeArray().forEach(function (it) {
        values[it.name] = it.value;
      });
      var errors = validate(values, constraintsFor(tab)) || null;
      if (errors) applyFieldErrors(errors);
      return errors;
    }

    function setPasswordMatchMessage() {
      var pass = $form.find('input[name="password"]').val() || '';
      var conf = $form.find('input[name="password_confirmation"]').val() || '';
      var $msg = $form.find('.password-match-error');
      if (!$msg.length) return;
      if (!conf) {
        $msg.text('').addClass('hidden');
        return;
      }
      if (pass !== conf) {
        $msg.text('Passwords do not match.').removeClass('hidden');
      } else {
        $msg.text('').addClass('hidden');
      }
    }

    $form.on('input', 'input[name="password"], input[name="password_confirmation"]', setPasswordMatchMessage);

    mountCard();

    $form.on('submit', function (e) {
      e.preventDefault();
      pendingSuccessRedirect = false;
      renderResponse($responseBox, '', '');

      if (!paymentIntentUrl || !registerUrl) {
        renderResponse($responseBox, 'error', 'Configuration error: URLs missing.');
        return;
      }

      var errors = validateForm();
      if (errors) {
        setPasswordMatchMessage();
        return;
      }

      var $groupSelect = $form.find('.tournament-group-select');
      if ($groupSelect.length && $groupSelect.prop('required') && !$groupSelect.val()) {
        $groupSelect.addClass('border-red-500');
        renderResponse($responseBox, 'error', 'Please select a group for this tournament.');
        return;
      }

      if (!stripe || !elements) {
        setCardError('Payment is unavailable. Please refresh the page.');
        return;
      }

      mountCard();
      if (!cardComplete) {
        setCardError('Card details are required.');
        $form.find('.stripe-card-element').addClass('border-red-500');
        return;
      }

      // Disable submit + show loader
      $btn.prop('disabled', true);
      if ($loader.length) $loader.removeClass('hidden');

      // Compute name
      var computed;
      if (tab === 'singles') {
        computed = ($.trim($form.find('[name="singles_first"]').val()) + ' ' + $.trim($form.find('[name="singles_last"]').val())).trim();
      } else {
        var a = ($.trim($form.find('[name="d1_first"]').val()) + ' ' + $.trim($form.find('[name="d1_last"]').val())).trim();
        var b = ($.trim($form.find('[name="d2_first"]').val()) + ' ' + $.trim($form.find('[name="d2_last"]').val())).trim();
        computed = (a + ' & ' + b).trim();
      }
      $form.find('.computed_name').val(computed);

      var leagueId = tab === 'singles' ? $form.find('select[name="tournament_singles"]').val() : $form.find('select[name="tournament_doubles"]').val();
      var skill = tab === 'singles' ? $form.find('select[name="skill_singles"]').val() : $form.find('select[name="skill_doubles"]').val();
      var groupCardId = $groupSelect.val();
      var email = tab === 'singles' ? $form.find('#singles_email').val() : $form.find('#doubles_email').val();

      if (!leagueId) {
        setCardError('Please select a tournament before payment.');
        $form.find('select[name="' + (tab === 'singles' ? 'tournament_singles' : 'tournament_doubles') + '"]').addClass('border-red-500');
        $btn.prop('disabled', false);
        if ($loader.length) $loader.addClass('hidden');
        return;
      }
      if (!groupCardId) {
        renderResponse($responseBox, 'error', 'Please select a group for this tournament.');
        $groupSelect.addClass('border-red-500');
        $btn.prop('disabled', false);
        if ($loader.length) $loader.addClass('hidden');
        return;
      }
      if (isGroupCardClosed(leagueId, groupCardId)) {
        renderResponse($responseBox, 'error', 'This group has started. Registration is closed for the selected group.');
        $btn.prop('disabled', false);
        if ($loader.length) $loader.addClass('hidden');
        return;
      }

      var formDataArray = $form.serializeArray();

      $.ajax({
        type: 'POST',
        url: paymentIntentUrl,
        contentType: 'application/json',
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || csrf },
        data: JSON.stringify({
          league_id: leagueId,
          registration_tab: tab,
          skill_level: skill,
          email: email,
        }),
      })
        .then(function (pi) {
          return stripe.confirmCardPayment(pi.client_secret, {
            payment_method: { card: card, billing_details: { email: email, name: computed || undefined } },
          });
        })
        .then(function (result) {
          if (result.error) throw new Error(result.error.message || 'Payment failed.');
          if (!result.paymentIntent || result.paymentIntent.status !== 'succeeded') throw new Error('Payment not completed.');

          $form.find('.payment_intent_id').val(result.paymentIntent.id);
          formDataArray = $form.serializeArray();

          return $.ajax({
            type: 'POST',
            url: registerUrl,
            data: formDataArray,
            dataType: 'html',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || csrf },
          });
        })
        .then(function (html) {
          $responseBox.html(html);
          var redirectUrl = $responseBox.find('[data-redirect-url]').attr('data-redirect-url');
          if (redirectUrl) {
            pendingSuccessRedirect = true;
            window.setTimeout(function () {
              window.location.href = redirectUrl;
            }, successRedirectDelayMs);
            return;
          }
          renderResponse($responseBox, 'success', 'Successfully registered.');
        })
        .fail(function (jqXHR) {
          var msg = 'Something went wrong.';
          if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
            msg = jqXHR.responseJSON.message;
          }
          // Server / payment errors: show only below Submit (response box), not under the card field
          setCardError('');
          var text = jqXHR && jqXHR.responseText ? String(jqXHR.responseText).trim() : '';
          var looksLikeHtml = text.length > 0 && text.charAt(0) === '<';
          if (looksLikeHtml) {
            $responseBox.html(jqXHR.responseText);
          } else {
            renderResponse($responseBox, 'error', msg);
          }
        })
        .always(function () {
          if (!pendingSuccessRedirect) {
            $btn.prop('disabled', false);
          }
          if ($loader.length) $loader.addClass('hidden');
        });
    });
  }

  $(function () {
    // Tabs
    $('#tab-singles').on('click', function () {
      setTabUI('singles');
      loadTournamentGroups('singles');
    });
    $('#tab-doubles').on('click', function () {
      setTabUI('doubles');
      loadTournamentGroups('doubles');
    });

    // Init both forms (independent validation + ajax)
    initRegisterForm('#singles-register-form');
    initRegisterForm('#doubles-register-form');

    $('#singles-register-form select[name="tournament_singles"]').on('change', function () {
      syncRegisterEntryFee($('#singles-register-form'));
      loadTournamentGroups('singles');
    });
    $('#doubles-register-form select[name="tournament_doubles"]').on('change', function () {
      syncRegisterEntryFee($('#doubles-register-form'));
      loadTournamentGroups('doubles');
    });
    syncRegisterEntryFee($('#singles-register-form'));
    syncRegisterEntryFee($('#doubles-register-form'));
    refreshTournamentGroupsForVisibleTab();
  });
})(window.jQuery);

