@foreach ($officialPartners as $partner)
    <li class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm">
        @if ($partner->website_url)
            <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer" class="flex h-full w-full items-center justify-center px-3">
                <img src="{{ asset($partner->logo_path) }}" alt="{{ $partner->name }}" class="max-h-16 max-w-32 object-contain" loading="lazy">
            </a>
        @else
            <img src="{{ asset($partner->logo_path) }}" alt="{{ $partner->name }}" class="max-h-16 max-w-32 object-contain" loading="lazy">
        @endif
    </li>
@endforeach
