<style>
    .match-scoreboard {
        border: 1px solid #e3ebe4;
        border-radius: 10px;
        background: #fff;
        padding: 0.65rem 0.85rem;
    }
    .match-scoreboard__layout {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 3.25rem;
    }
    .match-scoreboard__players {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.35rem;
    }
    .match-scoreboard__player-line {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .match-scoreboard__player-line.is-winner {
        color: #1b6b52;
    }
    .match-scoreboard__player-line.is-loser {
        color: #9aada0;
        font-weight: 600;
    }
    .match-scoreboard__totals {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.35rem;
        flex-shrink: 0;
    }
    .match-scoreboard__total-line {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.15rem;
        min-width: 2rem;
    }
    .match-scoreboard__total-line.is-winner .match-scoreboard__sets-num {
        color: #1b6b52;
    }
    .match-scoreboard__total-line.is-loser .match-scoreboard__sets-num {
        color: #9aada0;
    }
    .match-scoreboard__sets-num {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .match-scoreboard__caret {
        width: 0;
        height: 0;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-right: 6px solid #1e4fa8;
        flex-shrink: 0;
    }
    .match-scoreboard__set-cols {
        display: flex;
        align-items: stretch;
        gap: 0.65rem;
        padding-left: 0.75rem;
        border-left: 1px solid #d5e3d7;
        flex-shrink: 0;
    }
    .match-scoreboard__set-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-width: 1.25rem;
    }
    .match-scoreboard__set-game {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .match-scoreboard__set-game.is-winner {
        color: #1b6b52;
    }
    .match-scoreboard__set-game.is-loser {
        color: #9aada0;
    }
    .match-scoreboard__final {
        flex-shrink: 0;
        font-size: 0.72rem;
        font-weight: 600;
        color: #9ca3af;
        letter-spacing: 0.02em;
        padding-left: 0.35rem;
    }
    .match-scoreboard--walkover,
    .match-scoreboard--raw {
        padding: 0.65rem;
        text-align: center;
    }
    .match-scoreboard__walkover-label {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6b7280;
    }
    .match-scoreboard__walkover-score {
        margin: 0.25rem 0 0;
        font-weight: 700;
    }
    .match-scoreboard__raw-label {
        display: inline-flex;
        border-radius: 999px;
        background: #e8f5e9;
        padding: 0.35rem 0.75rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #2e7d32;
    }

    /* Score entry — compact set grid */
    .match-score-entry {
        display: flex;
        align-items: stretch;
        gap: 0.4rem;
        margin-top: 0.35rem;
        padding: 0.35rem 0.45rem;
        border: 1px solid #e3ebe4;
        border-radius: 8px;
        background: #fff;
    }
    .match-score-entry__labels {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.25rem;
        flex-shrink: 0;
        padding-top: 0.85rem;
    }
    .match-score-entry__who {
        font-size: 0.58rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b8f6a;
        line-height: 1.65rem;
    }
    .match-score-entry__cols {
        display: flex;
        align-items: stretch;
        gap: 0.35rem;
        flex: 1;
        justify-content: flex-start;
        padding-left: 0.35rem;
        border-left: 1px solid #d5e3d7;
    }
    .match-score-entry__col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.2rem;
        min-width: 2.1rem;
    }
    .match-score-entry__col-head {
        font-size: 0.55rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #9ca3af;
        letter-spacing: 0.04em;
    }
    .match-score-entry__input.admin-input {
        width: 2.1rem;
        min-height: 0;
        height: 1.65rem;
        padding: 0.1rem 0.2rem;
        text-align: center;
        font-weight: 700;
        font-size: 0.85rem;
        line-height: 1.2;
        border-radius: 6px;
        margin: 0;
    }
    .match-score-entry__input[type="number"]::-webkit-outer-spin-button,
    .match-score-entry__input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .match-score-entry__input[type="number"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    @media (max-width: 640px) {
        .match-scoreboard {
            padding: 0.5rem 0.55rem;
        }

        .match-scoreboard__layout {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }

        .match-scoreboard__player-line {
            white-space: normal;
            font-size: 0.85rem;
        }

        .match-scoreboard__scores {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .match-score-entry__grid {
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>
