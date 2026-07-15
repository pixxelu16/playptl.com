@extends('layouts.admin')

@section('title', 'Site Settings | '.config('app.name', 'playptl'))
@section('meta_description', 'Update website footer brand and contact details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Site Settings</h1>
                <p class="admin-card-text">Update the header logo, footer brand, and contact details shown on the website.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.contact-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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

            <h2 class="admin-card-title" style="font-size: 1.1rem; margin: 28px 0 12px;">Contact Details</h2>
            <p class="admin-card-text" style="margin-bottom: 20px;">Phone, email, and address shown in the footer contact column.</p>

            <div class="admin-form-grid">
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

            <h2 class="admin-card-title" style="font-size: 1.1rem; margin: 28px 0 12px;">Stripe Gateway Configuration</h2>
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
                    <input class="admin-input" id="stripe_test_publishable_key" type="text" name="stripe_test_publishable_key" value="{{ old('stripe_test_publishable_key', $stripe['test_publishable_key']) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="stripe_test_secret_key">Test Secret Key</label>
                    <input class="admin-input" id="stripe_test_secret_key" type="password" name="stripe_test_secret_key" value="{{ old('stripe_test_secret_key', $stripe['test_secret_key']) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="stripe_live_publishable_key">Live Publishable Key</label>
                    <input class="admin-input" id="stripe_live_publishable_key" type="text" name="stripe_live_publishable_key" value="{{ old('stripe_live_publishable_key', $stripe['live_publishable_key']) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="stripe_live_secret_key">Live Secret Key</label>
                    <input class="admin-input" id="stripe_live_secret_key" type="password" name="stripe_live_secret_key" value="{{ old('stripe_live_secret_key', $stripe['live_secret_key']) }}">
                </div>
            </div>

            <button class="admin-button" type="submit" style="margin-top: 28px;">Save Site Settings</button>
        </form>
    </section>
@endsection
