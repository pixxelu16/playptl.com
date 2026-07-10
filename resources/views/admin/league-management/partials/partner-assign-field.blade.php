@php
    $partnerOptions = $partnerOptionsByRegId[$reg->id] ?? [];
    $currentPartnerRegId = $currentPartnerRegIdByRegId[$reg->id] ?? null;
@endphp
<form method="POST" action="{{ route('admin.league-management.players.update-partner', [$league, $groupCard, $reg]) }}" class="admin-assign">
    @csrf
    @method('PUT')
    <select class="admin-input" name="partner_registration_id" aria-label="Choose partner" style="width:180px;max-width:180px;">
        <option value="">No partner</option>
        @foreach ($partnerOptions as $option)
            <option value="{{ $option['registration_id'] }}" @selected((int) $currentPartnerRegId === (int) $option['registration_id'])>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
    <button class="admin-button admin-button-secondary" type="submit">Save</button>
</form>
