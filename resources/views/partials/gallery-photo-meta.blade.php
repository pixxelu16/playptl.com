@php
    $leagueName = trim((string) ($item['leagueName'] ?? ''));
    $divisionLabel = trim((string) ($item['divisionLabel'] ?? ''));
    $matchLabel = trim((string) ($item['matchLabel'] ?? ''));
    $matchScore = trim((string) ($item['matchScore'] ?? ''));
    $hasMeta = $leagueName !== '' || $divisionLabel !== '' || $matchLabel !== '' || $matchScore !== '';
    $compact = (bool) ($compact ?? false);
    $below = (bool) ($below ?? false);
    $overlay = (bool) ($overlay ?? false);
@endphp
@if ($hasMeta)
    <div @class([
        'gallery-photo-meta',
        'gallery-photo-meta--compact' => $compact && ! $below && ! $overlay,
        'gallery-photo-meta--below' => $below,
        'gallery-photo-meta--overlay' => $overlay,
    ])>
        @if ($leagueName !== '')
            <p class="gallery-photo-meta__league">{{ $leagueName }}</p>
        @endif
        @if ($divisionLabel !== '')
            <p class="gallery-photo-meta__division">{{ $divisionLabel }}</p>
        @endif
        @if ($matchLabel !== '')
            <p class="gallery-photo-meta__match">{{ $matchLabel }}</p>
        @endif
        @if ($matchScore !== '')
            <p class="gallery-photo-meta__score">{{ $matchScore }}</p>
        @endif
    </div>
@endif
