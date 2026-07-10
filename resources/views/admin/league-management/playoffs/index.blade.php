@extends('layouts.admin')

@section('title', 'Playoffs | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Single elimination playoff bracket for this division.')

@push('styles')
    @include('partials.match-scoreboard-styles')
    <style>
        .playoffs-subpage {
            --pg: #55A64E;
            --pg-dark: #2f7a2a;
            --pg-soft: #e8f6ea;
            --pg-line: #c5dfc6;
            --pg-shadow: rgba(85, 166, 78, 0.08);
        }

        .playoffs-empty {
            margin-top: 1.5rem;
        }
        .playoffs-empty p {
            margin: 0.5rem 0 0;
            max-width: 36rem;
        }

        .playoff-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem 1.25rem;
            margin: 1rem 0 1.25rem;
            padding: 0.85rem 1rem;
            background: var(--pg-soft);
            border: 1px solid var(--pg-line);
            border-radius: 12px;
        }
        .playoff-toolbar__form {
            margin: 0;
            flex-shrink: 0;
        }
        .playoff-toolbar__hint {
            margin: 0;
            font-size: 0.88rem;
            color: #3d5c3a;
            line-height: 1.45;
            flex: 1 1 220px;
        }
        .playoff-toolbar__warn {
            display: block;
            margin-top: 0.25rem;
            color: #6a4a00;
            font-size: 0.82rem;
        }

        .playoff-flow {
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
        .playoff-flow__item {
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
        .playoff-flow__item:hover {
            background: var(--pg-soft);
        }
        .playoff-flow__item.is-done .playoff-flow__step {
            background: var(--pg);
            color: #fff;
        }
        .playoff-flow__step {
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
        .playoff-flow__label {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--pg-dark);
        }
        .playoff-flow__meta {
            font-size: 0.68rem;
            color: #5a7a58;
        }
        .playoff-flow__arrow {
            color: #9ab89a;
            font-size: 0.65rem;
            padding: 0 0.15rem;
        }

        .playoff-stack {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .playoff-round {
            scroll-margin-top: 1rem;
        }
        .playoff-round__header {
            margin-bottom: 0.85rem;
            padding-bottom: 0.65rem;
            border-bottom: 2px solid var(--pg-line);
        }
        .playoff-round__heading {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.5rem 0.75rem;
        }
        .playoff-round__step {
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
        .playoff-round__title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--pg-dark);
        }
        .playoff-round__count {
            font-size: 0.78rem;
            font-weight: 600;
            color: #5a7a58;
        }
        .playoff-round__hint {
            margin: 0.45rem 0 0;
            font-size: 0.84rem;
            color: #4a6b48;
            line-height: 1.4;
        }
        .playoff-round__grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .playoff-match-card {
            background: #fff;
            border: 1px solid var(--pg-line);
            border-radius: 14px;
            padding: 0.75rem 0.85rem 0.85rem;
            box-shadow: 0 6px 18px var(--pg-shadow);
        }
        .playoff-match-card.is-complete {
            border-color: #8fc88a;
            background: linear-gradient(180deg, #f7fdf7 0%, #fff 100%);
        }
        .playoff-match-card.is-locked {
            opacity: 0.92;
            background: #fafcfa;
        }
        .playoff-match-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.55rem;
        }
        .playoff-match-card__label {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--pg-dark);
        }
        .playoff-match-card__status {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.2rem 0.45rem;
            border-radius: 6px;
            background: var(--pg);
            color: #fff;
        }
        .playoff-match-card__status--pending {
            background: #dcefdc;
            color: var(--pg-dark);
        }
        .playoff-match-card__status--locked {
            background: #fff3d6;
            color: #6a4a00;
        }
        .playoff-match-card__lock {
            margin: 0 0 0.5rem;
            font-size: 0.78rem;
            color: #6a4a00;
            background: #fff8e6;
            border: 1px solid #f0e0b8;
            border-radius: 8px;
            padding: 0.35rem 0.5rem;
            line-height: 1.35;
        }
        .playoff-match-card__fieldset {
            border: 0;
            margin: 0;
            padding: 0;
            min-width: 0;
        }
        .playoff-match-card__fieldset:disabled {
            opacity: 0.75;
        }
        .playoff-match-card__versus {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 0.45rem 0.35rem;
            align-items: start;
            margin-bottom: 0.65rem;
        }
        .playoff-match-card__side-label {
            display: block;
            font-size: 0.58rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--pg-dark);
            margin-bottom: 0.2rem;
        }
        .playoff-match-card__name {
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
        .playoff-match-card__name--tbd {
            font-weight: 600;
            font-size: 0.78rem;
            color: #6a7a68;
            font-style: italic;
        }
        .playoff-match-card__player-select {
            font-weight: 600;
            text-align: left;
        }
        .playoff-match-card__vs {
            align-self: center;
            font-size: 0.72rem;
            font-weight: 900;
            color: #7a9a78;
            padding-bottom: 0.35rem;
        }
        .playoff-match-card__meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.45rem 0.55rem;
            margin-bottom: 0.6rem;
        }
        .playoff-match-card__score {
            grid-column: 1 / -1;
        }
        .playoff-match-card label {
            display: block;
            font-size: 0.58rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--pg-dark);
            margin-bottom: 0.12rem;
        }
        .playoff-match-card .admin-input {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.85rem;
            padding: 0.38rem 0.5rem;
            min-height: 0;
        }
        .playoff-match-card__save {
            width: 100%;
            padding: 0.45rem 0.75rem;
            font-size: 0.88rem;
        }
        .playoff-match-card__winner {
            margin: 0.55rem 0 0;
            padding-top: 0.5rem;
            border-top: 1px dashed var(--pg-line);
            font-size: 0.82rem;
            color: #3d5c3a;
        }

        @media (max-width: 640px) {
            .playoff-match-card__versus {
                grid-template-columns: 1fr;
            }
            .playoff-match-card__vs {
                text-align: center;
                padding: 0;
            }
            .playoff-round__grid {
                grid-template-columns: 1fr;
            }
        }
        .playoff-match-card .match-result-fields {
            margin-bottom: 0.65rem;
        }
        .match-result-fields {
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--pg-line);
            border-radius: 10px;
            background: #f9fcf9;
        }
        .match-result-fields__type {
            border: 0;
            margin: 0 0 0.55rem;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1.25rem;
            align-items: center;
        }
        .match-result-fields__legend {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--pg-dark);
            padding: 0;
            margin: 0 0.5rem 0 0;
        }
        .match-result-fields__radio {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .match-result-fields__walkover label,
        .match-result-fields__score-wrap label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--pg-dark);
            margin-bottom: 0.2rem;
        }
        .match-result-fields__note {
            margin: 0.35rem 0 0;
            font-size: 0.78rem;
            color: #555;
            line-height: 1.35;
        }
        .match-result-fields__score-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--pg-dark);
            margin-bottom: 0.2rem;
        }
    </style>
    <script src="{{ asset('admin/js/match-result-fields.js') }}" defer></script>
@endpush

@section('content')
    <section class="admin-card match-schedule-page">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Playoffs — {{ $league->name }}</h1>
                <p class="admin-card-text">
                    Group: <strong>{{ $groupCard->name }}</strong>
                    @if ($ageGroupKey)
                        · Age: <strong>{{ $ageGroupKey }}</strong>
                    @endif
                    @if (isset($activeGroup) && $activeGroup)
                        · Subgroup: <strong>{{ $activeGroup->name }}</strong>
                    @endif
                </p>
            </div>
            @include('admin.league-management.partials.group-card-header-actions', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'activeGroupId' => $activeGroupId,
                'playerSchemaReady' => $playerSchemaReady,
                'active' => 'playoffs',
            ])
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('admin.league-management.playoffs.index-body')
    </section>
@endsection
