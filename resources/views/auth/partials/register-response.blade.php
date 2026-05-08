@php
    $type = $type ?? 'error'; // success | error
    $message = $message ?? '';
    $redirectUrl = $redirectUrl ?? null;
@endphp

<div class="rounded-[10px] border px-3 py-2 {{ $type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-900' }}">
    {{ $message }}
</div>

@if ($redirectUrl)
    <div data-redirect-url="{{ $redirectUrl }}"></div>
@endif

