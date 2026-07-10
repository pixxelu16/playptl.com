<style>
    .playoffs-public {
        --pg: #55A64E;
        --pg-dark: #2f7a2a;
        --pg-soft: #e8f6ea;
        --pg-line: #c5dfc6;
        --pg-shadow: rgba(85, 166, 78, 0.08);
    }

    .playoffs-public .playoff-flow {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.5rem;
        margin-bottom: 1.5rem;
        padding: 0.65rem 0.75rem;
        background: #fff;
        border: 1px solid var(--pg-line);
        border-radius: 12px;
        overflow-x: auto;
    }
    .playoffs-public .playoff-flow__item {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.1rem;
        padding: 0.45rem 0.65rem;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        min-width: 5.5rem;
        transition: background 0.15s ease;
    }
    .playoffs-public .playoff-flow__item:hover {
        background: var(--pg-soft);
    }
    .playoffs-public .playoff-flow__item.is-done .playoff-flow__step {
        background: var(--pg);
        color: #fff;
    }
    .playoffs-public .playoff-flow__step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        background: #dcefdc;
        color: var(--pg-dark);
    }
    .playoffs-public .playoff-flow__label {
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--pg-dark);
    }
    .playoffs-public .playoff-flow__meta {
        font-size: 0.68rem;
        color: #5a7a58;
    }
    .playoffs-public .playoff-flow__arrow {
        color: #9ab89a;
        font-size: 0.65rem;
        padding: 0 0.15rem;
    }

    .playoffs-public .playoff-stack {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .playoffs-public .playoff-round {
        scroll-margin-top: 1rem;
    }
    .playoffs-public .playoff-round__header {
        margin-bottom: 0.85rem;
        padding-bottom: 0.65rem;
        border-bottom: 2px solid var(--pg-line);
    }
    .playoffs-public .playoff-round__heading {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.5rem 0.75rem;
    }
    .playoffs-public .playoff-round__step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.6rem;
        height: 1.6rem;
        padding: 0 0.35rem;
        border-radius: 8px;
        background: var(--pg);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .playoffs-public .playoff-round__title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--pg-dark);
    }
    .playoffs-public .playoff-round__count {
        font-size: 0.78rem;
        font-weight: 600;
        color: #5a7a58;
    }
    .playoffs-public .playoff-round__hint {
        margin: 0.45rem 0 0;
        font-size: 0.84rem;
        color: #4a6b48;
        line-height: 1.4;
    }
    .playoffs-public .playoff-round__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .playoffs-public .playoff-match-card {
        background: #fff;
        border: 1px solid var(--pg-line);
        border-radius: 14px;
        padding: 0.75rem 0.85rem 0.85rem;
        box-shadow: 0 6px 18px var(--pg-shadow);
    }
    .playoffs-public .playoff-match-card.is-complete {
        border-color: #8fc88a;
        background: linear-gradient(180deg, #f7fdf7 0%, #fff 100%);
    }
    .playoffs-public .playoff-match-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.55rem;
    }
    .playoffs-public .playoff-match-card__label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--pg-dark);
    }
    .playoffs-public .playoff-match-card__status {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.2rem 0.45rem;
        border-radius: 6px;
        background: var(--pg);
        color: #fff;
    }
    .playoffs-public .playoff-match-card__status--pending {
        background: #dcefdc;
        color: var(--pg-dark);
    }
    .playoffs-public .playoff-match-card__versus {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 0.45rem 0.35rem;
        align-items: start;
        margin-bottom: 0.65rem;
    }
    .playoffs-public .playoff-match-card__side-label {
        display: block;
        font-size: 0.58rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--pg-dark);
        margin-bottom: 0.2rem;
    }
    .playoffs-public .playoff-match-card__name {
        margin: 0;
        padding: 0.45rem 0.5rem;
        min-height: 2.35rem;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.25;
        color: #1a3d18;
        background: #f4faf4;
        border: 1px solid var(--pg-line);
        border-radius: 8px;
    }
    .playoffs-public .playoff-match-card__name--tbd {
        font-weight: 600;
        font-size: 0.78rem;
        color: #6a7a68;
        font-style: italic;
    }
    .playoffs-public .playoff-match-card__vs {
        align-self: center;
        font-size: 0.72rem;
        font-weight: 900;
        color: #7a9a78;
        padding-bottom: 0.35rem;
    }
    .playoffs-public .playoff-match-card__details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.75rem;
        font-size: 0.8rem;
        color: #4a6b48;
        margin-bottom: 0.5rem;
    }
    .playoffs-public .playoff-match-card__score-line {
        font-weight: 600;
    }
    .playoffs-public .playoff-match-card__winner {
        margin: 0.55rem 0 0;
        padding-top: 0.5rem;
        border-top: 1px dashed var(--pg-line);
        font-size: 0.82rem;
        color: #3d5c3a;
    }

    @media (max-width: 640px) {
        .playoffs-public .playoff-match-card__versus {
            grid-template-columns: 1fr;
        }
        .playoffs-public .playoff-match-card__vs {
            text-align: center;
            padding: 0;
        }
        .playoffs-public .playoff-round__grid {
            grid-template-columns: 1fr;
        }
    }
</style>
