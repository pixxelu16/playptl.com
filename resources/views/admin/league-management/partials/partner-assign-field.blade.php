@php
    $partnerOptions = $partnerOptionsByRegId[$reg->id] ?? [];
    $currentPartnerRegId = $currentPartnerRegIdByRegId[$reg->id] ?? null;
    $currentPartnerUserId = $currentPartnerUserIdByRegId[$reg->id] ?? null;
@endphp
<form method="POST" action="{{ route('admin.league-management.players.update-partner', [$league, $groupCard, $reg]) }}" class="admin-assign">
    @csrf
    @method('PUT')
    <select class="admin-input select2-search" name="partner_registration_id" aria-label="Choose partner" data-select2-width="210px" style="width:210px;">
        <option value="">No partner</option>
        @foreach ($partnerOptions as $option)
            @php
                $isSelected = ($currentPartnerRegId !== null && (int) $currentPartnerRegId === (int) $option['registration_id'])
                    || ($currentPartnerUserId !== null && (int) $currentPartnerUserId === (int) $option['user_id']);
            @endphp
            <option value="{{ $option['registration_id'] }}" @selected($isSelected)>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
    <button class="admin-button admin-button-secondary" type="submit">Save</button>
</form>
