@extends('layouts.website')

@section('title', 'Privacy Policy | Premier Tennis League')
@section('meta_description', 'Privacy Policy for Premier Tennis League — how we collect, use, and protect your information.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        @include('partials.site-page-hero', [
            'heroBannerPath' => \App\Models\SiteSetting::banners()['privacy_banner_path'],
            'heroBreadcrumb' => 'Privacy Policy',
            'heroTitleLight' => 'PRIVACY',
            'heroTitleAccent' => 'POLICY',
            'heroMetaItems' => [
                'Last updated: '.now()->format('F j, Y'),
            ],
        ])

        <section class="bg-[#E4F7E7] pb-6 pt-10 font-sans antialiased sm:pb-8 sm:pt-12">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
                    <div class="space-y-8 text-[15px] leading-relaxed text-[#333333]">
                        <p>
                            Premier Tennis League ("PTL", "we", "us", or "our") operates the playptl platform for competitive tennis tournaments, player registration, league management, match scheduling, and charity fundraising. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website and services.
                        </p>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">1. Information We Collect</h2>
                            <p class="mb-3">We may collect the following types of information:</p>
                            <ul class="list-disc space-y-2 pl-5">
                                <li><strong>Account information:</strong> name, email address, phone number, password (stored securely), and profile details such as city, state, gender, and profile photo.</li>
                                <li><strong>Tournament information:</strong> league registrations, division selections, skill level, age group, partner details, match results, and uploaded match photos.</li>
                                <li><strong>Payment information:</strong> registration fees and charity donations are processed through Stripe. We do not store full card numbers on our servers; payment data is handled by Stripe according to their privacy practices.</li>
                                <li><strong>Technical information:</strong> IP address, browser type, device information, and usage data collected through cookies and similar technologies for security and site functionality.</li>
                            </ul>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">2. How We Use Your Information</h2>
                            <ul class="list-disc space-y-2 pl-5">
                                <li>Create and manage your player, organiser, or admin account.</li>
                                <li>Process tournament registrations, payments, and charity contributions.</li>
                                <li>Schedule matches, display standings, playoffs, and league results.</li>
                                <li>Send service-related emails such as registration confirmations, password resets, match updates, and partner notifications.</li>
                                <li>Improve platform security, performance, and user experience.</li>
                                <li>Comply with legal obligations and enforce our Terms and Conditions.</li>
                            </ul>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">3. Sharing of Information</h2>
                            <p class="mb-3">We may share information only as needed to operate the platform:</p>
                            <ul class="list-disc space-y-2 pl-5">
                                <li>With payment processors (such as Stripe) to complete transactions.</li>
                                <li>With tournament organisers and league administrators for match management and player coordination.</li>
                                <li>With other players when required for doubles pairings, schedules, or published match results.</li>
                                <li>When required by law, court order, or to protect the rights and safety of PTL, our users, or the public.</li>
                            </ul>
                            <p class="mt-3">We do not sell your personal information to third parties.</p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">4. Data Retention</h2>
                            <p>
                                We retain account and tournament records for as long as your account is active or as needed to provide services, maintain league history, resolve disputes, and meet legal requirements. You may request account updates or deletion by contacting us using the details in the footer.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">5. Security</h2>
                            <p>
                                We use reasonable administrative, technical, and physical safeguards to protect your information. However, no online system is completely secure, and we cannot guarantee absolute security of data transmitted over the internet.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">6. Your Rights</h2>
                            <p>
                                Depending on your location, you may have the right to access, correct, update, or delete your personal information, or to object to certain processing. To make a request, contact us at
                                <a href="mailto:{{ $contactSettings['email'] ?? 'player.one@example.com' }}" class="font-semibold text-[#2E7D32] hover:underline">{{ $contactSettings['email'] ?? 'player.one@example.com' }}</a>.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">7. Children's Privacy</h2>
                            <p>
                                PTL may include junior divisions managed through parent or guardian registration. We do not knowingly collect personal information from children without appropriate consent where required by law.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">8. Changes to This Policy</h2>
                            <p>
                                We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated date. Continued use of the platform after changes means you accept the revised policy.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">9. Contact Us</h2>
                            <p>
                                For privacy-related questions, contact Premier Tennis League at
                                <a href="mailto:{{ $contactSettings['email'] ?? 'player.one@example.com' }}" class="font-semibold text-[#2E7D32] hover:underline">{{ $contactSettings['email'] ?? 'player.one@example.com' }}</a>
                                or call
                                <a href="tel:{{ preg_replace('/\D+/', '', $contactSettings['phone'] ?? '+919876543210') }}" class="font-semibold text-[#2E7D32] hover:underline">{{ $contactSettings['phone'] ?? '+91 98765 43210' }}</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
