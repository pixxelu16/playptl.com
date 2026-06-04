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
      setPartnerSkillLocked($form, '', false);
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
            if (res.skill_locked && res.skill_level) {
              setPartnerSkillLocked($form, res.skill_level, true);
            } else {
              setPartnerSkillLocked($form, '', false);
            }
            loadProfileAssignedGroup('doubles');
            setStatus('info', res.message || 'Tell your buddy to please log in with their account and see match details there.');
            return;
          }
          clearPartnerFields();
          setPartnerSkillLocked($form, '', false);
          loadProfileAssignedGroup('doubles');
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

  function closedGroupCardSet() {
    var raw = $('#profile-league-registration').attr('data-closed-group-cards');
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

  function partnerSkillValue($form) {
    var $locked = $form.find('.partner-skill-locked');
    if ($locked.length && !$locked.hasClass('hidden')) {
      return String($locked.data('skill-value') || '').trim();
    }
    return String($form.find('select[name="d2_skill"]').val() || '').trim();
  }

  function setPartnerSkillLocked($form, skill, locked) {
    var $select = $form.find('select[name="d2_skill"]');
    var $locked = $form.find('.partner-skill-locked');
    $form.find('.partner-skill-hidden').remove();

    if (locked && skill) {
      $select.addClass('hidden').prop('disabled', true).removeAttr('name').val(skill);
      $locked.val(skill).data('skill-value', skill).removeClass('hidden');
      $form.find('.partner-skill-field').append(
        $('<input>', {
          type: 'hidden',
          name: 'd2_skill',
          class: 'partner-skill-hidden',
          value: skill,
        }),
      );
    } else {
      $locked.addClass('hidden').val('').removeData('skill-value');
      $select.removeClass('hidden').prop('disabled', false).attr('name', 'd2_skill');
      if (!locked) {
        $select.val('');
      }
    }
  }

  function leagueFeesMap() {
    var raw = $('#profile-league-registration').attr('data-league-fees');
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

  function syncProfileEntryFee($form) {
    if (!$form || !$form.length) return;
    var tab = $form.data('registration-tab') || 'singles';
    var leagueId = $form.find('select[name="tournament_' + tab + '"]').val();
    var amount = entryFeeForLeague(leagueId, tab);
    $form.find('.entry-fee-amount').text(amount);
  }

  function tournamentGroupsUrl() {
    return $('#profile-league-registration').attr('data-tournament-groups-url') || '';
  }

  function fixedSkillLevel() {
    return String($('#profile-league-registration').attr('data-fixed-skill') || '').trim();
  }

  function profileFormForTab(tab) {
    return tab === 'doubles' ? $('#profile-doubles-league-form') : $('#profile-singles-league-form');
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

  function loadProfileAssignedGroup(tab) {
    var $form = profileFormForTab(tab);
    if (!$form.length) return;

    var ui = assignedGroupUi($form, tab);
    var leagueId = $form.find('select[name="tournament_' + tab + '"]').val();
    var skill = fixedSkillLevel();
    var skillTwo = tab === 'doubles' ? partnerSkillValue($form) : '';
    var url = tournamentGroupsUrl();

    resetAssignedGroupUi(ui);

    if (!leagueId || !skill || (tab === 'doubles' && !skillTwo)) {
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

    var params = {
      league_id: leagueId,
      tab: tab,
      skill_level: skill,
    };
    if (tab === 'doubles') {
      params.skill_level_2 = skillTwo;
    }

    $.getJSON(url, params)
      .done(function (payload) {
        applyAssignedGroupPayload(ui, payload, tab === 'doubles');
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

  function refreshProfileTournamentGroups() {
    var tab = $('#profile-doubles-league-form').hasClass('hidden') ? 'singles' : 'doubles';
    loadProfileAssignedGroup(tab);
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

    var stripe = stripeKey && window.Stripe ? Stripe(stripeKey, { advancedFraudSignals: false }) : null;
    var elements = stripe
      ? stripe.elements({
          wallets: { applePay: 'never', googlePay: 'never' },
        })
      : null;
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
        wallets: { applePay: 'never', googlePay: 'never' },
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

      var tournamentName = tab === 'singles' ? 'tournament_singles' : 'tournament_doubles';
      var skill = fixedSkillLevel();
      var leagueId = $.trim($form.find('[name="' + tournamentName + '"]').val() || '');
      var groupCardId = $.trim($form.find('.tournament-group-id').val() || '');

      $form.find('select, input').removeClass('border-red-500');
      if (!skill) {
        renderResponse($responseBox, 'error', 'Set your skill level on Personal Information first.');
        return;
      }
      if (!leagueId) {
        $form.find('[name="' + tournamentName + '"]').addClass('border-red-500');
        renderResponse($responseBox, 'error', 'Please select a tournament.');
        return;
      }
      if (!groupCardId) {
        renderResponse($responseBox, 'error', 'Select a tournament to see your group assignment.');
        refreshProfileTournamentGroups();
        return;
      }
      if (tab === 'singles' && isDivisionClosed(leagueId, tab, skill)) {
        renderResponse($responseBox, 'error', 'This group has started. Registration is closed for this skill level.');
        return;
      }

      if (isGroupCardClosed(leagueId, groupCardId)) {
        renderResponse($responseBox, 'error', 'This group has started. Registration is closed.');
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
        var partnerSkill = partnerSkillValue($form);
        if (!partnerSkill || partnerSkill === 'not-sure') {
          $form.find('select[name="d2_skill"], .partner-skill-locked').addClass('border-red-500');
          missing = true;
        }
        if (missing) {
          renderResponse($responseBox, 'error', 'Please complete second player details and skill level.');
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
    $('#profile-tab-singles').on('click', function () {
      setTabUI('singles');
      loadProfileAssignedGroup('singles');
    });
    $('#profile-tab-doubles').on('click', function () {
      setTabUI('doubles');
      loadProfileAssignedGroup('doubles');
    });
    var initial = $('#profile-league-registration').data('initial-tab') || 'singles';
    setTabUI(initial);
    initProfileLeagueForm('#profile-singles-league-form');
    initProfileLeagueForm('#profile-doubles-league-form');

    $('#profile-singles-league-form select[name="tournament_singles"]').on('change', function () {
      syncProfileEntryFee($('#profile-singles-league-form'));
      loadProfileAssignedGroup('singles');
    });
    $('#profile-doubles-league-form select[name="tournament_doubles"]').on('change', function () {
      syncProfileEntryFee($('#profile-doubles-league-form'));
      loadProfileAssignedGroup('doubles');
    });
    $('#profile-doubles-league-form select[name="d2_skill"]').on('change', function () {
      loadProfileAssignedGroup('doubles');
    });
    syncProfileEntryFee($('#profile-singles-league-form'));
    syncProfileEntryFee($('#profile-doubles-league-form'));
    refreshProfileTournamentGroups();
  });
})(window.jQuery);
