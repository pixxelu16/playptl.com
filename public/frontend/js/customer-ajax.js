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

  function showToast(message, type) {
    type = type || 'success';
    var bg = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    var icon = type === 'success' ? '<i class="fa-solid fa-circle-check mr-2"></i>' : '<i class="fa-solid fa-circle-exclamation mr-2"></i>';
    var $toast = $(
      '<div class="fixed top-5 right-5 z-[9999] flex items-center rounded-lg px-4 py-3 text-white shadow-lg transition-all duration-300 transform translate-y-[-20px] opacity-0 ' + bg + '">' +
        icon +
        '<span class="font-medium text-sm">' + escapeHtml(message) + '</span>' +
      '</div>'
    );
    $('body').append($toast);
    
    $toast.get(0).offsetHeight;
    $toast.removeClass('translate-y-[-20px] opacity-0').addClass('translate-y-0 opacity-100');
    
    setTimeout(function () {
      $toast.removeClass('translate-y-0 opacity-100').addClass('translate-y-[-20px] opacity-0');
      setTimeout(function () {
        $toast.remove();
      }, 300);
    }, 4000);
  }

  function clearFieldErrors($form) {
    $form.find('input,select,textarea').removeClass('border-red-500');
    $form.find('.validation-error-msg').remove();
  }

  function applyFieldErrors($form, errors) {
    if (!errors) return;
    var firstErrElement = null;
    Object.keys(errors).forEach(function (name) {
      var $el = $form.find('[name="' + name + '"]');
      if ($el.length) {
        $el.addClass('border-red-500');
        if ($el.attr('type') === 'password') {
          $el.parent().next('.validation-error-msg').remove();
        } else {
          $el.parent().find('.validation-error-msg').remove();
        }
        var msg = errors[name][0];
        if (msg.indexOf('^') === 0) {
          msg = msg.substring(1);
        } else {
          msg = msg.charAt(0).toUpperCase() + msg.slice(1);
        }
        var $target = $el;
        if ($el.attr('type') === 'password' && $el.parent().hasClass('relative')) {
          $target = $el.parent();
        }
        $target.after('<span class="validation-error-msg text-red-500 text-xs mt-1 block" style="color: #dc2626; font-size: 12px; margin-top: 4px; display: block;">' + msg + '</span>');
        if (!firstErrElement) {
          firstErrElement = $el.get(0);
        }
      }
    });
    if (firstErrElement) {
      firstErrElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
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
    if (tab === 'singles') {
      loadSinglesAssignedGroup();
      return;
    }

    loadDoublesAssignedGroup();
  }

  function assignedGroupUi($form, tab) {
    return {
      $wrap: $form.find('.tournament-group-wrap[data-tab="' + tab + '"]'),
      $preview: $form.find('.tournament-group-wrap[data-tab="' + tab + '"] .tournament-group-preview'),
      $hiddenId: $form.find('.tournament-group-wrap[data-tab="' + tab + '"] .tournament-group-id'),
      $hint: $form.find('.tournament-group-wrap[data-tab="' + tab + '"] .tournament-group-hint'),
      $loading: $form.find('.tournament-group-wrap[data-tab="' + tab + '"] .tournament-group-loading'),
      $error: $form.find('.tournament-group-wrap[data-tab="' + tab + '"] .tournament-group-error'),
    };
  }

  function resetAssignedGroupUi(ui) {
    ui.$error.addClass('hidden').text('');
    ui.$preview.removeClass('border-red-500');
  }

  function loadSinglesAssignedGroup() {
    var $form = $('#singles-register-form');
    if (!$form.length) return;

    var ui = assignedGroupUi($form, 'singles');
    var leagueId = $form.find('select[name="tournament_singles"]').val();
    var skill = $form.find('select[name="skill_singles"]').val();
    var url = tournamentGroupsUrl();

    resetAssignedGroupUi(ui);

    if (!leagueId || !skill) {
      ui.$wrap.addClass('hidden');
      ui.$preview.val('');
      ui.$hiddenId.val('');
      ui.$hint.addClass('hidden');
      ui.$loading.addClass('hidden');
      return;
    }

    ui.$wrap.removeClass('hidden');

    if (!url) {
      ui.$preview.val('');
      ui.$hiddenId.val('');
      ui.$error.removeClass('hidden').text('Could not load group assignment.');
      return;
    }

    ui.$loading.removeClass('hidden');
    ui.$hint.addClass('hidden');

    $.getJSON(url, { league_id: leagueId, tab: 'singles', skill_level: skill })
      .done(function (payload) {
        applyAssignedGroupPayload(ui, payload);
      })
      .fail(function () {
        ui.$preview.val('');
        ui.$hiddenId.val('');
        ui.$error.removeClass('hidden').text('Could not load your group. Please try again.');
      })
      .always(function () {
        ui.$loading.addClass('hidden');
      });
  }

  function loadDoublesAssignedGroup() {
    var $form = $('#doubles-register-form');
    if (!$form.length) return;

    var ui = assignedGroupUi($form, 'doubles');
    var leagueId = $form.find('select[name="tournament_doubles"]').val();
    var skillOne = $form.find('select[name="skill_doubles"]').val();
    var skillTwo = $form.find('select[name="d2_skill"]').val();
    var url = tournamentGroupsUrl();

    resetAssignedGroupUi(ui);

    if (!leagueId || !skillOne || !skillTwo) {
      ui.$wrap.addClass('hidden');
      ui.$preview.val('');
      ui.$hiddenId.val('');
      ui.$hint.addClass('hidden');
      ui.$loading.addClass('hidden');
      return;
    }

    ui.$wrap.removeClass('hidden');

    if (!url) {
      ui.$preview.val('');
      ui.$hiddenId.val('');
      ui.$error.removeClass('hidden').text('Could not load group assignment.');
      return;
    }

    ui.$loading.removeClass('hidden');
    ui.$hint.addClass('hidden');

    $.getJSON(url, {
      league_id: leagueId,
      tab: 'doubles',
      skill_level: skillOne,
      skill_level_2: skillTwo,
    })
      .done(function (payload) {
        applyAssignedGroupPayload(ui, payload, true);
      })
      .fail(function () {
        ui.$preview.val('');
        ui.$hiddenId.val('');
        ui.$error.removeClass('hidden').text('Could not load your group. Please try again.');
      })
      .always(function () {
        ui.$loading.addClass('hidden');
      });
  }

  function applyAssignedGroupPayload(ui, payload, showAverage) {
    var assigned = payload && payload.assigned_group ? payload.assigned_group : null;
    var averageSkill = payload && payload.average_skill ? payload.average_skill : null;

    if (!assigned) {
      ui.$preview.val('');
      ui.$hiddenId.val('');
      ui.$error.removeClass('hidden').text('No group matches your skill level for this tournament.');
      return;
    }

    if (!assigned.registration_open) {
      ui.$preview.val(assigned.label || assigned.name || 'Group');
      ui.$hiddenId.val('');
      ui.$error
        .removeClass('hidden')
        .text(assigned.closed_reason || 'Registration is closed for this group.');
      ui.$preview.addClass('border-red-500');
      return;
    }

    ui.$preview.val(assigned.label || assigned.name || 'Group');
    ui.$hiddenId.val(String(assigned.id));
    ui.$hint.removeClass('hidden');
    if (showAverage && averageSkill) {
      ui.$hint.text('Team average skill: ' + averageSkill + '. Subgroup is assigned automatically.');
    } else {
      ui.$hint.text('Subgroup (A, B, C…) is assigned automatically when you register.');
    }
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

    $form.on('focus input change', 'input, select, textarea', function () {
      var $el = $(this);
      $el.removeClass('border-red-500');
      $el.parent().find('.validation-error-msg').remove();
      if ($el.attr('type') === 'password') {
        $el.parent().next('.validation-error-msg').remove();
      }
    });

    $form.on('input', 'input[name="phone_singles"], input[name="phone_doubles"], input[name="d2_phone"]', function () {
      var v = String($(this).val() || '');
      var cleaned = v.replace(/\D+/g, '');
      if (cleaned !== v) $(this).val(cleaned);
    });



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
        d2_city: { presence: { allowEmpty: false } },
        d2_state: { presence: { allowEmpty: false } },
        d2_age_group: { presence: { allowEmpty: false } },
        d2_skill: { presence: { allowEmpty: false } },
        d2_sex: { presence: { allowEmpty: false } },
      });
    }

    function validateForm() {
      if (!window.validate) {
        renderResponse($responseBox, 'error', 'Validation library missing. Please refresh.');
        return { _validation: ['missing'] };
      }
      clearFieldErrors($form);
      var values = {};
      $form.serializeArray().forEach(function (it) {
        values[it.name] = it.value;
      });
      var errors = validate(values, constraintsFor(tab)) || null;
      if (errors) applyFieldErrors($form, errors);
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

      var $groupHidden = $form.find('.tournament-group-id');
      if (tab === 'singles' || tab === 'doubles') {
        if (!$groupHidden.val()) {
          $form.find('.tournament-group-preview').addClass('border-red-500');
          $form.find('.tournament-group-error').removeClass('hidden').text(
            tab === 'singles'
              ? 'Please select tournament and skill level to assign your group.'
              : 'Please select tournament and both skill levels to assign your group.'
          );
          renderResponse(
            $responseBox,
            'error',
            tab === 'singles'
              ? 'Your group could not be assigned. Check tournament and skill level.'
              : 'Your group could not be assigned. Check tournament and both players\' skill levels.'
          );
          return;
        }
      } else if ($groupSelect.length && $groupSelect.prop('required') && !$groupSelect.val()) {
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
      var $groupSelect = $form.find('.tournament-group-select');
      var groupCardId = $form.find('.tournament-group-id').val() || ($groupSelect.length ? $groupSelect.val() : '');
      var email = tab === 'singles' ? $form.find('#singles_email').val() : $form.find('#doubles_email').val();

      if (!leagueId) {
        setCardError('Please select a tournament before payment.');
        $form.find('select[name="' + (tab === 'singles' ? 'tournament_singles' : 'tournament_doubles') + '"]').addClass('border-red-500');
        $btn.prop('disabled', false);
        if ($loader.length) $loader.addClass('hidden');
        return;
      }
      if (!groupCardId) {
        renderResponse($responseBox, 'error', tab === 'singles' || tab === 'doubles'
          ? 'Your group could not be assigned. Check tournament and skill level(s).'
          : 'Please select a group for this tournament.');
        if (tab === 'singles' || tab === 'doubles') {
          $form.find('.tournament-group-preview').addClass('border-red-500');
        } else if ($groupSelect.length) {
          $groupSelect.addClass('border-red-500');
        }
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
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || csrf,
              'Accept': 'application/json'
            },
          });
        })
        .then(function (response) {
          var redirectUrl = null;
          var message = '';

          if (typeof response === 'string') {
            var text = response.trim();
            if (text.charAt(0) === '{') {
              try {
                response = JSON.parse(text);
              } catch (e) {}
            }
          }

          if (typeof response === 'object' && response !== null) {
            redirectUrl = response.redirect_url;
            message = response.message;
          } else {
            var $html = $(response);
            redirectUrl = $html.filter('[data-redirect-url]').attr('data-redirect-url') || $html.find('[data-redirect-url]').attr('data-redirect-url');
            message = $html.text().trim();
          }

          if (redirectUrl) {
            pendingSuccessRedirect = true;
            showToast(message || 'Registration successful! Redirecting...', 'success');
            window.setTimeout(function () {
              window.location.href = redirectUrl;
            }, successRedirectDelayMs);
            return;
          }
          $responseBox.html(response);
        })
        .fail(function (jqXHR) {
          var msg = 'Something went wrong.';
          if (jqXHR && jqXHR.status === 422) {
            var errors = null;
            try {
              errors = JSON.parse(jqXHR.responseText).errors;
            } catch (err) {}
            if (errors) {
              applyFieldErrors($form, errors);
              var errList = '<ul class="list-disc list-inside text-sm text-red-700 space-y-1">';
              Object.keys(errors).forEach(function (name) {
                errList += '<li>' + escapeHtml(errors[name][0]) + '</li>';
              });
              errList += '</ul>';
              $responseBox.html(
                '<div class="rounded-[10px] border border-red-200 bg-red-50 p-3 mb-4">' +
                  errList +
                '</div>'
              );
              return;
            }
          }
          if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
            msg = jqXHR.responseJSON.message;
          } else if (jqXHR && jqXHR.message) {
            msg = jqXHR.message;
          }
          // Server / payment errors: show only below Submit (response box), not under the card field
          setCardError('');
          var text = jqXHR && jqXHR.responseText ? String(jqXHR.responseText).trim() : '';
          var looksLikeHtml = text.length > 0 && text.charAt(0) === '<';
          if (looksLikeHtml && text.indexOf('<!DOCTYPE') === -1 && text.indexOf('<html') === -1) {
            $responseBox.html(text);
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

  function initRoleRegisterForm(formSelector) {
    var $form = $(formSelector);
    if (!$form.length) return;

    var $responseBox = $form.find('.custom_register_form_res');
    var $loader = $form.find('.common-loader');
    var $btn = $form.find('.disable-button');
    var registerUrl = $form.attr('action') || '';

    function validateRoleForm() {
      if (!window.validate) {
        renderResponse($responseBox, 'error', 'Validation library missing. Please refresh.');
        return { _validation: ['missing'] };
      }
      clearFieldErrors($form);
      var values = {};
      $form.serializeArray().forEach(function (it) {
        values[it.name] = it.value;
      });
      var constraints = {
        first_name: { presence: { allowEmpty: false } },
        last_name: { presence: { allowEmpty: false } },
        email: { presence: { allowEmpty: false }, email: true },
        phone: { presence: { allowEmpty: false } },
        password: { presence: { allowEmpty: false }, length: { minimum: 8 } },
        password_confirmation: {
          presence: { allowEmpty: false },
          equality: { attribute: 'password', message: '^Passwords do not match.' },
        },
        city: { presence: { allowEmpty: false } },
        state: { presence: { allowEmpty: false } },
      };
      var errors = validate(values, constraints) || null;
      if (errors) applyFieldErrors($form, errors);
      return errors;
    }

    // Live UX
    $form.on('focus input change', 'input, select, textarea', function () {
      var $el = $(this);
      $el.removeClass('border-red-500');
      $el.parent().find('.validation-error-msg').remove();
      if ($el.attr('type') === 'password') {
        $el.parent().next('.validation-error-msg').remove();
      }
    });

    $form.on('input', 'input[name="phone"]', function () {
      var v = String($(this).val() || '');
      var cleaned = v.replace(/\D+/g, '');
      if (cleaned !== v) $(this).val(cleaned);
    });

    $form.on('submit', function (e) {
      e.preventDefault();

      clearFieldErrors($form);
      $responseBox.html('');

      var errors = validateRoleForm();
      if (errors) {
        return;
      }

      $btn.prop('disabled', true);
      if ($loader.length) $loader.removeClass('hidden');

      $.ajax({
        type: 'POST',
        url: registerUrl,
        data: $form.serialize(),
        dataType: 'html',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
          'Accept': 'application/json'
        },
      })
        .then(function (response) {
          var redirectUrl = null;
          var message = '';

          if (typeof response === 'string') {
            var text = response.trim();
            if (text.charAt(0) === '{') {
              try {
                response = JSON.parse(text);
              } catch (e) {}
            }
          }

          if (typeof response === 'object' && response !== null) {
            redirectUrl = response.redirect_url;
            message = response.message;
          } else {
            var $html = $(response);
            redirectUrl = $html.filter('[data-redirect-url]').attr('data-redirect-url') || $html.find('[data-redirect-url]').attr('data-redirect-url');
            message = $html.text().trim();
          }

          if (redirectUrl) {
            showToast(message || 'Registration successful! Redirecting...', 'success');
            window.setTimeout(function () {
              window.location.href = redirectUrl;
            }, 2000);
            return;
          }
          $responseBox.html(response);
        })
        .fail(function (jqXHR) {
          var msg = 'Something went wrong.';
          if (jqXHR && jqXHR.status === 422) {
            var errors = null;
            try {
              errors = JSON.parse(jqXHR.responseText).errors;
            } catch (err) {}
            if (errors) {
              applyFieldErrors($form, errors);
              var errList = '<ul class="list-disc list-inside text-sm text-red-700 space-y-1">';
              Object.keys(errors).forEach(function (name) {
                errList += '<li>' + escapeHtml(errors[name][0]) + '</li>';
              });
              errList += '</ul>';
              $responseBox.html(
                '<div class="rounded-[10px] border border-red-200 bg-red-50 p-3 mb-4">' +
                  errList +
                '</div>'
              );

              var firstErrElement = null;
              Object.keys(errors).forEach(function (name) {
                var $el = $form.find('[name="' + name + '"]');
                if ($el.length && !firstErrElement) {
                  firstErrElement = $el;
                }
              });
              if (firstErrElement) {
                $('html, body').animate({ scrollTop: firstErrElement.offset().top - 120 }, 300);
              }
              return;
            }
          }
          if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.message) {
            msg = jqXHR.responseJSON.message;
          } else if (jqXHR && jqXHR.message) {
            msg = jqXHR.message;
          }
          renderResponse($responseBox, 'error', msg);
        })
        .always(function () {
          $btn.prop('disabled', false);
          if ($loader.length) $loader.addClass('hidden');
        });
    });
  }

  var currentTab = 'singles';

  $(function () {
    // Tabs
    $('#tab-singles').on('click', function () {
      currentTab = 'singles';
      setTabUI('singles');
      loadTournamentGroups('singles');
    });
    $('#tab-doubles').on('click', function () {
      currentTab = 'doubles';
      setTabUI('doubles');
      loadTournamentGroups('doubles');
    });

    // Init forms (independent validation + ajax)
    initRegisterForm('#singles-register-form');
    initRegisterForm('#doubles-register-form');
    initRoleRegisterForm('#role-register-form');

    // Role selector changes (modern radio group click handler)
    $('#role-radio-group').on('click', '.role-radio-card', function (e) {
      if (e.target.tagName === 'INPUT') return;
      var $card = $(this);
      var $radio = $card.find('input[name="registration_role"]');
      var val = $radio.val();

      $radio.prop('checked', true);

      // Update radio card styles
      $('#role-radio-group .role-radio-card').each(function () {
        var $c = $(this);
        var isSelected = $c.attr('data-role') === val;
        $c.toggleClass('border-[#5DA44E] bg-[#E4F7E7]', isSelected);
        $c.toggleClass('border-[#dddddd] bg-white', !isSelected);
        
        var $icon = $c.find('i');
        if (isSelected) {
          $icon.removeClass('text-[#666]').addClass('text-[#5DA44E]');
        } else {
          $icon.removeClass('text-[#5DA44E]').addClass('text-[#666]');
        }
      });

      if (val === 'player') {
        $('#tab-singles, #tab-doubles').removeClass('hidden');
        setTabUI(currentTab);
        $('#role-register-form').addClass('hidden');
      } else {
        $('#tab-singles, #tab-doubles').addClass('hidden');
        $('#singles-register-form, #doubles-register-form').addClass('hidden');
        $('#role-register-form').removeClass('hidden');
        $('#role-register-form input[name="role"]').val(val);
      }
    });

    $('#singles-register-form select[name="tournament_singles"]').on('change', function () {
      syncRegisterEntryFee($('#singles-register-form'));
      loadSinglesAssignedGroup();
    });
    $('#singles-register-form select[name="skill_singles"]').on('change', function () {
      loadSinglesAssignedGroup();
    });
    $('#doubles-register-form select[name="tournament_doubles"]').on('change', function () {
      syncRegisterEntryFee($('#doubles-register-form'));
      loadDoublesAssignedGroup();
    });
    $('#doubles-register-form select[name="skill_doubles"], #doubles-register-form select[name="d2_skill"]').on('change', function () {
      loadDoublesAssignedGroup();
    });
    syncRegisterEntryFee($('#singles-register-form'));
    syncRegisterEntryFee($('#doubles-register-form'));
    refreshTournamentGroupsForVisibleTab();
  });
})(window.jQuery);

