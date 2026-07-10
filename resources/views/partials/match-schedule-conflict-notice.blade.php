@php
    $lines = $lines ?? [];
@endphp
@if ($lines !== [])
    <div class="match-schedule-conflict" role="alert">
        @foreach ($lines as $line)
            <p class="match-schedule-conflict__line">{{ $line }}</p>
        @endforeach
    </div>
@endif

@once
    @push('styles')
        <style>
            .match-schedule-conflict {
                margin-top: 0.5rem;
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid #fecaca;
                background: #fef2f2;
            }
            .match-schedule-conflict__line {
                margin: 0;
                font-size: 11px;
                line-height: 1.45;
                font-weight: 600;
                color: #b91c1c;
            }
            .match-schedule-conflict__line + .match-schedule-conflict__line {
                margin-top: 0.35rem;
            }
            .admin-match-schedule-conflict {
                margin: 0.65rem 0 0;
            }
        </style>
    @endpush
@endonce
