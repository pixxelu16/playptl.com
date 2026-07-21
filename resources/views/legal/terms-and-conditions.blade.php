@extends('layouts.website')

@section('title', 'Terms and Conditions | Premier Tennis League')
@section('meta_description', 'Terms and Conditions for using Premier Tennis League tournaments, registrations, and platform services.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        @include('partials.site-page-hero', [
            'heroBannerPath' => \App\Models\SiteSetting::banners()['terms_banner_path'],
            'heroBreadcrumb' => 'Terms and Conditions',
            'heroTitleLight' => 'TERMS AND',
            'heroTitleAccent' => 'CONDITIONS',
            'heroMetaItems' => [
                'Last updated: '.now()->format('F j, Y'),
            ],
        ])

        <section class="bg-[#E4F7E7] pb-6 pt-10 font-sans antialiased sm:pb-8 sm:pt-12">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <div class="rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
                    <div class="space-y-8 text-[15px] leading-relaxed text-[#333333]">
                        <p>
                            Welcome to Premier Tennis League ("PTL"). By accessing our website, registering for an account, entering a tournament, or using any of our services, you agree to these Terms and Conditions. If you do not agree, please do not use the platform.
                        </p>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">1. About the Platform</h2>
                            <p>
                                PTL provides an online platform for competitive tennis league management, including player registration, tournament divisions, match scheduling, standings, playoffs, gallery uploads, and charity fundraising. League rules, formats, and schedules may vary by tournament and are published on the relevant league pages.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">2. Eligibility and Accounts</h2>
                            <ul class="list-disc space-y-2 pl-5">
                                <li>You must provide accurate and complete registration information.</li>
                                <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                                <li>You are responsible for all activity that occurs under your account.</li>
                                <li>PTL may suspend or terminate accounts that violate these terms or league policies.</li>
                            </ul>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">3. Tournament Registration and Fees</h2>
                            <ul class="list-disc space-y-2 pl-5">
                                <li>Entry fees for singles and doubles divisions are displayed at registration and must be paid to complete enrollment unless otherwise stated.</li>
                                <li>Payments are processed securely through Stripe. By submitting payment, you authorize the charge for the selected tournament and division.</li>
                                <li>Refund eligibility, withdrawal deadlines, and division changes are subject to the rules of the specific league or tournament.</li>
                                <li>PTL and tournament organisers may reject or cancel registrations that do not meet eligibility requirements.</li>
                            </ul>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">4. Player Conduct and Fair Play</h2>
                            <ul class="list-disc space-y-2 pl-5">
                                <li>Players must follow published match schedules, scoring rules, and sportsmanship standards.</li>
                                <li>Match results, uploads, and playoff progression must be submitted honestly and within stated deadlines.</li>
                                <li>Abusive behaviour, harassment, cheating, or repeated failure to complete scheduled matches may result in penalties, disqualification, or account suspension.</li>
                            </ul>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">5. Match Content and Gallery Uploads</h2>
                            <p>
                                By uploading photos or other match-related content, you confirm that you have the right to share that material and grant PTL a non-exclusive right to display it on the platform for league and promotional purposes. PTL may remove content that is inappropriate, misleading, or violates third-party rights.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">6. Charity Donations</h2>
                            <p>
                                Charity contributions made through the platform are voluntary and processed via Stripe. Donation amounts and supported causes are displayed before payment. Unless otherwise stated for a specific campaign, donations are generally non-refundable once completed.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">7. Platform Availability</h2>
                            <p>
                                We aim to keep the platform available and accurate, but we do not guarantee uninterrupted access. Maintenance, technical issues, scheduling changes, or force majeure events may affect availability. PTL is not liable for delays caused by factors outside reasonable control.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">8. Limitation of Liability</h2>
                            <p>
                                To the fullest extent permitted by law, PTL, its organisers, and affiliates are not liable for indirect, incidental, or consequential damages arising from your use of the platform, participation in tournaments, or reliance on published schedules and results. Participation in tennis activities involves inherent physical risk, and players participate at their own responsibility.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">9. Privacy</h2>
                            <p>
                                Your use of the platform is also governed by our
                                <a href="{{ route('privacy-policy') }}" class="font-semibold text-[#2E7D32] hover:underline">Privacy Policy</a>,
                                which explains how we collect and use personal information.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">10. Changes to These Terms</h2>
                            <p>
                                PTL may update these Terms and Conditions at any time. Updated terms will be posted on this page with a revised date. Continued use of the platform after changes constitutes acceptance of the updated terms.
                            </p>
                        </div>

                        <div>
                            <h2 class="mb-3 text-[18px] font-bold uppercase tracking-[0.06em] text-[#1B3022]">11. Contact</h2>
                            <p>
                                For questions about these terms, contact Premier Tennis League at
                                <a href="mailto:{{ $contactSettings['email'] ?? 'player.one@example.com' }}" class="font-semibold text-[#2E7D32] hover:underline">{{ $contactSettings['email'] ?? 'player.one@example.com' }}</a>
                                or
                                <a href="tel:{{ preg_replace('/\D+/', '', $contactSettings['phone'] ?? '+919876543210') }}" class="font-semibold text-[#2E7D32] hover:underline">{{ $contactSettings['phone'] ?? '+91 98765 43210' }}</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
