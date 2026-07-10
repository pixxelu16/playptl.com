@props(['user'])
@php
    $rawName = (string) ($user?->name ?? '');
    $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);
@endphp
{{ $displayName !== '' ? $displayName : '—' }}
