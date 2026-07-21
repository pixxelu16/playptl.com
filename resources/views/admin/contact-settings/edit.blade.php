@extends('layouts.admin')

@section('title', 'Site Settings | '.config('app.name', 'playptl'))
@section('meta_description', 'Update website footer brand and contact details from the admin dashboard.')

@push('styles')
<style>
    /* Tabs Navigation */
    .settings-tabs {
        display: flex;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
        gap: 8px;
        overflow-x: auto;
    }
    .settings-tab-btn {
        padding: 10px 16px;
        font-weight: 600;
        font-size: 14px;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .settings-tab-btn:hover {
        color: #5DA44E;
    }
    .settings-tab-btn.is-active {
        color: #5DA44E;
        border-bottom-color: #5DA44E;
    }

    /* Tab Content panels */
    .settings-tab-panel {
        display: none;
        animation: fadeIn 0.2s ease-in-out;
    }
    .settings-tab-panel.is-active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Site Settings</h1>
                <p class="admin-card-text">Update the header logo, footer brand, email settings, commission structure, and payment gateways.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        {{-- Tabs Navigation --}}
        <div class="settings-tabs" role="tablist">
            <button type="button" class="settings-tab-btn is-active" data-tab="general" role="tab" aria-selected="true">General Settings</button>
            <button type="button" class="settings-tab-btn" data-tab="banners" role="tab" aria-selected="false">Page Banners</button>
            <button type="button" class="settings-tab-btn" data-tab="stripe" role="tab" aria-selected="false">Stripe Gateway</button>
            <button type="button" class="settings-tab-btn" data-tab="smtp" role="tab" aria-selected="false">Email (SMTP)</button>
            <button type="button" class="settings-tab-btn" data-tab="test-smtp" role="tab" aria-selected="false">Test Email</button>
            <button type="button" class="settings-tab-btn" data-tab="commission" role="tab" aria-selected="false">Commissions</button>
        </div>

        {{-- 1. General Settings Panel --}}
        <div id="panel-general" class="settings-tab-panel is-active" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="setting_section" value="general">

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 12px;">Header Logo</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Logo shown in the website header navigation.</p>

                <div class="admin-form-grid">
                    <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center;">
                        <img src="{{ asset($header['logo_path']) }}" alt="Header logo preview" width="152" height="120" style="width:152px;height:120px;object-fit:contain;border:1px solid #d7ead9;border-radius:8px;background:#fff;padding:8px;">
                        <div style="flex:1;">
                            <label class="admin-label" for="header_logo">Header Logo</label>
                            <input class="admin-input" id="header_logo" type="file" name="header_logo" accept="image/*">
                            <p class="admin-card-text" style="margin-top: 8px; font-size: 13px; opacity: .8;">JPG, PNG, WebP, or SVG. Max 2MB.</p>
                        </div>
                    </div>
                </div>

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin: 28px 0 12px;">Footer Brand</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Logo and description shown in the left footer column.</p>

                <div class="admin-form-grid">
                    <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center;">
                        <img src="{{ asset($footer['logo_path']) }}" alt="Footer logo preview" width="152" height="120" style="width:152px;height:120px;object-fit:contain;border:1px solid #d7ead9;border-radius:8px;background:#fff;padding:8px;">
                        <div style="flex:1;">
                            <label class="admin-label" for="footer_logo">Footer Logo</label>
                            <input class="admin-input" id="footer_logo" type="file" name="footer_logo" accept="image/*">
                            <p class="admin-card-text" style="margin-top: 8px; font-size: 13px; opacity: .8;">JPG, PNG, WebP, or SVG. Max 2MB.</p>
                        </div>
                    </div>

                    <div class="admin-form-group" style="grid-column: 1 / -1;">
                        <label class="admin-label" for="footer_description">Footer Description</label>
                        <textarea class="admin-input" id="footer_description" name="footer_description" rows="4" required>{{ old('footer_description', $footer['description']) }}</textarea>
                    </div>
                </div>

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin: 28px 0 12px;">Site & Contact Details</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Site title, phone, email, and address used across branding and email templates.</p>

                <div class="admin-form-grid">
                    <div class="admin-form-group" style="grid-column: 1 / -1;">
                        <label class="admin-label" for="site_title">Site Title</label>
                        <input class="admin-input" id="site_title" type="text" name="site_title" value="{{ old('site_title', $contact['site_title'] ?? 'Premier Tennis League') }}" placeholder="e.g. Premier Tennis League" required>
                        <p class="admin-card-text" style="margin-top: 6px; font-size: 13px; opacity: .8;">Used as the primary brand name in emails, sign-offs, and footers.</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="contact_phone">Phone</label>
                        <input class="admin-input" id="contact_phone" type="text" name="contact_phone" value="{{ old('contact_phone', $contact['phone']) }}" required>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="contact_email">Email</label>
                        <input class="admin-input" id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $contact['email']) }}" required>
                    </div>

                    <div class="admin-form-group" style="grid-column: 1 / -1;">
                        <label class="admin-label" for="contact_address">Address</label>
                        <textarea class="admin-input" id="contact_address" name="contact_address" rows="3" required>{{ old('contact_address', $contact['address']) }}</textarea>
                    </div>
                </div>

                <button class="admin-button" type="submit" style="margin-top: 28px;">Save General Settings</button>
            </form>
        </div>

        {{-- 2. Page Banners Panel --}}
        <div id="panel-banners" class="settings-tab-panel" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="setting_section" value="banners">

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 12px;">Page Banners</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Upload custom hero background images for frontend pages. Recommended size: 1920x600px.</p>

                <div class="admin-form-grid">
                    @php
                        $bannerFields = [
                            ['name' => 'home_banner',           'label' => 'Home Page Banner',           'key' => 'home_banner_path'],
                            ['name' => 'gallery_banner',        'label' => 'Gallery Banner',             'key' => 'gallery_banner_path'],
                            ['name' => 'charity_banner',        'label' => 'Charity Banner',             'key' => 'charity_banner_path'],
                            ['name' => 'league_banner',         'label' => 'Tournament Listing Banner',   'key' => 'league_banner_path'],
                            ['name' => 'league_details_banner', 'label' => 'Tournament Details Banner',   'key' => 'league_details_banner_path'],
                            ['name' => 'mentors_banner',        'label' => 'Mentors Listing Banner',     'key' => 'mentors_banner_path'],
                            ['name' => 'coaches_banner',        'label' => 'Coaches Listing Banner',     'key' => 'coaches_banner_path'],
                            ['name' => 'mentor_profile_banner', 'label' => 'Mentor Profile Banner',      'key' => 'mentor_profile_banner_path'],
                            ['name' => 'coach_profile_banner',  'label' => 'Coach Profile Banner',       'key' => 'coach_profile_banner_path'],
                            ['name' => 'privacy_banner',        'label' => 'Privacy Policy Banner',      'key' => 'privacy_banner_path'],
                            ['name' => 'terms_banner',          'label' => 'Terms & Conditions Banner',  'key' => 'terms_banner_path'],
                        ];
                    @endphp

                    @foreach ($bannerFields as $b)
                        <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center; background:#fff; padding:12px; border:1px solid #e2e8f0; border-radius:8px;">
                            <img src="{{ asset($banners[$b['key']] ?? 'frontend/images/hero_tennis_banner.png') }}" alt="{{ $b['label'] }}" width="160" height="70" style="width:160px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;">
                            <div style="flex:1;">
                                <label class="admin-label" for="{{ $b['name'] }}">{{ $b['label'] }}</label>
                                <input class="admin-input" id="{{ $b['name'] }}" type="file" name="{{ $b['name'] }}" accept="image/*">
                                <p class="admin-card-text" style="margin-top: 4px; font-size: 12px; opacity: .75;">Max 4MB. JPG, PNG, or WebP.</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="admin-button" type="submit" style="margin-top: 28px;">Save Page Banners</button>
            </form>
        </div>

        {{-- 3. Stripe Gateway Panel --}}
        <div id="panel-stripe" class="settings-tab-panel" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="setting_section" value="stripe">

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 12px;">Stripe Gateway Configuration</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Set Stripe operating mode, currency, and API credentials.</p>

                <div class="admin-form-grid">
                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_mode">Payment Mode</label>
                        <select class="admin-input" id="stripe_mode" name="stripe_mode" required>
                            <option value="test" {{ old('stripe_mode', $stripe['mode']) === 'test' ? 'selected' : '' }}>Test Mode</option>
                            <option value="live" {{ old('stripe_mode', $stripe['mode']) === 'live' ? 'selected' : '' }}>Live Mode</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_currency">Currency</label>
                        <select class="admin-input" id="stripe_currency" name="stripe_currency" required>
                            @foreach(['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'INR'] as $currency)
                                <option value="{{ $currency }}" {{ old('stripe_currency', $stripe['currency']) === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_test_publishable_key">Test Publishable Key</label>
                        <input class="admin-input" id="stripe_test_publishable_key" type="text" name="stripe_test_publishable_key" value="{{ old('stripe_test_publishable_key', $stripe['test_publishable_key']) }}" placeholder="Enter Test Publishable Key">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_test_secret_key">Test Secret Key</label>
                        <input class="admin-input" id="stripe_test_secret_key" type="password" name="stripe_test_secret_key" value="{{ old('stripe_test_secret_key', $stripe['test_secret_key']) }}" placeholder="Enter Test Secret Key">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_live_publishable_key">Live Publishable Key</label>
                        <input class="admin-input" id="stripe_live_publishable_key" type="text" name="stripe_live_publishable_key" value="{{ old('stripe_live_publishable_key', $stripe['live_publishable_key']) }}" placeholder="Enter Live Publishable Key">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="stripe_live_secret_key">Live Secret Key</label>
                        <input class="admin-input" id="stripe_live_secret_key" type="password" name="stripe_live_secret_key" value="{{ old('stripe_live_secret_key', $stripe['live_secret_key']) }}" placeholder="Enter Live Secret Key">
                    </div>
                </div>

                <button class="admin-button" type="submit" style="margin-top: 28px;">Save Stripe Settings</button>
            </form>
        </div>

        {{-- 4. SMTP Settings Panel --}}
        <div id="panel-smtp" class="settings-tab-panel" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="setting_section" value="smtp">

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 12px;">Email (SMTP) Configuration</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">
                    Configure transactional email delivery. Changes take effect immediately without editing <code>.env</code>.
                </p>

                <div class="admin-form-grid">
                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_mailer">Mail Driver</label>
                        <select class="admin-input" id="smtp_mailer" name="smtp_mailer" required>
                            <option value="smtp" {{ old('smtp_mailer', $smtp['mailer']) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="log"  {{ old('smtp_mailer', $smtp['mailer']) === 'log'  ? 'selected' : '' }}>Log (testing)</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_encryption">Encryption</label>
                        <select class="admin-input" id="smtp_encryption" name="smtp_encryption">
                            <option value="tls" {{ old('smtp_encryption', $smtp['encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ old('smtp_encryption', $smtp['encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_host">SMTP Host</label>
                        <input class="admin-input" id="smtp_host" type="text" name="smtp_host"
                               value="{{ old('smtp_host', $smtp['host']) }}" placeholder="smtp.mailgun.org">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_port">SMTP Port</label>
                        <input class="admin-input" id="smtp_port" type="number" name="smtp_port" min="1" max="65535"
                               value="{{ old('smtp_port', $smtp['port']) }}" placeholder="587">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_username">Username</label>
                        <input class="admin-input" id="smtp_username" type="text" name="smtp_username"
                               value="{{ old('smtp_username', $smtp['username']) }}" placeholder="SMTP username or API key">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_password">Password</label>
                        <input class="admin-input" id="smtp_password" type="password" name="smtp_password"
                               value="{{ old('smtp_password', $smtp['password']) }}" placeholder="Enter SMTP password">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_from_address">From Email Address</label>
                        <input class="admin-input" id="smtp_from_address" type="email" name="smtp_from_address" required
                               value="{{ old('smtp_from_address', $smtp['from_address']) }}" placeholder="noreply@yourdomain.com">
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label" for="smtp_from_name">From Name</label>
                        <input class="admin-input" id="smtp_from_name" type="text" name="smtp_from_name" required
                               value="{{ old('smtp_from_name', $smtp['from_name']) }}" placeholder="Premier Tennis League">
                    </div>
                </div>

                <button class="admin-button" type="submit" style="margin-top: 28px;">Save SMTP Settings</button>
            </form>
        </div>

        {{-- 5. Test Email Panel --}}
        <div id="panel-test-smtp" class="settings-tab-panel" role="tabpanel">
            <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 8px;">Test Email Configuration</h2>
            <p class="admin-card-text" style="margin-bottom: 16px;">
                Send a test email to verify your SMTP settings and check for server delivery errors.
            </p>

            <form method="POST" action="{{ route('admin.contact-settings.test-smtp') }}" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; max-width:600px;">
                @csrf
                <div style="flex:1; min-width:260px;">
                    <label class="admin-label" for="test_email" style="display:block; margin-bottom:6px; font-size:13px; font-weight:600;">Recipient Email Address</label>
                    <input class="admin-input" id="test_email" type="email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" placeholder="e.g. yourname@domain.com" required style="width:100%; height:42px; border-radius:8px; border:1px solid #CBD5E1; padding:0 14px; font-size:14px;">
                </div>
                <button type="submit" class="admin-button" style="height:42px; padding:0 20px; background:#2563EB; color:#ffffff; font-size:14px; font-weight:600; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-paper-plane"></i> Send Test Email
                </button>
            </form>
        </div>

        {{-- 6. Commission Settings Panel --}}
        <div id="panel-commission" class="settings-tab-panel" role="tabpanel">
            <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="setting_section" value="commission">

                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 12px;">Booking Commission Settings</h2>
                <p class="admin-card-text" style="margin-bottom: 20px;">Set the platform commission percentage deducted from each booking. The Mentor/Coach receives the remaining amount.</p>

                <div class="admin-form-grid">
                    <div class="admin-form-group">
                        <label class="admin-label" for="mentor_commission_percent">Mentor Commission (%)</label>
                        <input class="admin-input" id="mentor_commission_percent" type="number"
                               name="mentor_commission_percent" min="0" max="100" step="0.01"
                               value="{{ old('mentor_commission_percent', $mentorCommission) }}" required>
                        <p class="admin-card-text" style="margin-top:6px;font-size:13px;opacity:.8;">
                            e.g. 20 means 20% goes to the platform, 80% to the Mentor.
                        </p>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label" for="coach_commission_percent">Coach Commission (%)</label>
                        <input class="admin-input" id="coach_commission_percent" type="number"
                               name="coach_commission_percent" min="0" max="100" step="0.01"
                               value="{{ old('coach_commission_percent', $coachCommission) }}" required>
                        <p class="admin-card-text" style="margin-top:6px;font-size:13px;opacity:.8;">
                            e.g. 20 means 20% goes to the platform, 80% to the Coach.
                        </p>
                    </div>
                </div>

                <button class="admin-button" type="submit" style="margin-top: 28px;">Save Commission Settings</button>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('.settings-tab-btn');
        const panels = document.querySelectorAll('.settings-tab-panel');

        // Restore active tab from localStorage if available
        const activeTabKey = 'admin_settings_active_tab';
        const savedTab = localStorage.getItem(activeTabKey);
        if (savedTab) {
            const matchedBtn = document.querySelector(`.settings-tab-btn[data-tab="${savedTab}"]`);
            if (matchedBtn) {
                switchTab(savedTab, matchedBtn);
            }
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTab = this.getAttribute('data-tab');
                switchTab(targetTab, this);
            });
        });

        function switchTab(tabId, activeBtn) {
            // Update buttons
            tabBtns.forEach(btn => {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-selected', 'false');
            });
            activeBtn.classList.add('is-active');
            activeBtn.setAttribute('aria-selected', 'true');

            // Update panels
            panels.forEach(panel => {
                panel.classList.remove('is-active');
            });
            const targetPanel = document.getElementById(`panel-${tabId}`);
            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }
            if (tabId === 'smtp') {
                const testPanel = document.getElementById('panel-smtp-test');
                if (testPanel) testPanel.classList.add('is-active');
            }

            // Save state
            localStorage.setItem(activeTabKey, tabId);
        }
    });
</script>
@endpush
