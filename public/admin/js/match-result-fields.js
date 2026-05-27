(function () {
    function buildScoreFromSetBoxes(root) {
        var parts = [];
        for (var n = 1; n <= 3; n++) {
            var homeInput = root.querySelector('[name="set_' + n + '_home"]');
            var awayInput = root.querySelector('[name="set_' + n + '_away"]');
            if (!homeInput || !awayInput) {
                continue;
            }
            var home = String(homeInput.value || '').trim();
            var away = String(awayInput.value || '').trim();
            if (home === '' && away === '') {
                continue;
            }
            if (home !== '' && away !== '') {
                parts.push(parseInt(home, 10) + '-' + parseInt(away, 10));
            }
        }
        return parts.join(', ');
    }

    function syncScoreHidden(root) {
        var scoreInput = root.querySelector('[data-score-input]');
        if (!scoreInput) {
            return;
        }
        var walkover = root.querySelector('input[data-result-type-radio][value="walkover"]');
        if (walkover && walkover.checked) {
            scoreInput.value = '';
            return;
        }
        scoreInput.value = buildScoreFromSetBoxes(root);
    }

    function syncResultFields(root) {
        var walkover = root.querySelector('input[data-result-type-radio][value="walkover"]');
        var isWalkover = walkover && walkover.checked;
        var walkPanel = root.querySelector('[data-walkover-panel]');
        var scorePanel = root.querySelector('[data-score-panel]');
        var walkedSelect = root.querySelector('[data-walked-off-select]');
        var setInputs = root.querySelectorAll('[data-set-home], [data-set-away]');

        if (walkPanel) {
            walkPanel.hidden = !isWalkover;
        }
        if (scorePanel) {
            scorePanel.hidden = isWalkover;
        }
        setInputs.forEach(function (input) {
            input.disabled = isWalkover;
        });
        if (walkedSelect) {
            walkedSelect.disabled = !isWalkover;
            if (!isWalkover) {
                walkedSelect.value = '';
            }
        }
        syncScoreHidden(root);
    }

    document.querySelectorAll('[data-match-result-fields]').forEach(function (root) {
        root.querySelectorAll('[data-result-type-radio]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                syncResultFields(root);
            });
        });
        root.querySelectorAll('[data-set-home], [data-set-away]').forEach(function (input) {
            input.addEventListener('input', function () {
                syncScoreHidden(root);
            });
        });
        syncResultFields(root);
    });
})();
