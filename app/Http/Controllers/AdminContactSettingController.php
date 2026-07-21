<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\PasswordEncryptionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminContactSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.contact-settings.edit', [
            'contact'           => SiteSetting::contact(),
            'header'            => SiteSetting::header(),
            'footer'            => SiteSetting::footer(),
            'banners'           => SiteSetting::banners(),
            'stripe'            => SiteSetting::stripe(),
            'mentorCommission'  => SiteSetting::mentorCommissionPercent(),
            'coachCommission'   => SiteSetting::coachCommissionPercent(),
            'smtp'              => SiteSetting::smtp(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = $request->input('setting_section', 'all');

        if ($section === 'general' || $section === 'all') {
            $validated = $request->validate([
                'header_logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
                'footer_description' => ['required', 'string', 'max:1000'],
                'footer_logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
                'contact_phone'      => ['required', 'string', 'max:32'],
                'contact_email'      => ['required', 'email', 'max:255'],
                'contact_address'    => ['required', 'string', 'max:500'],
                'site_title'         => ['required', 'string', 'max:255'],
            ]);

            $this->updateLogoSetting(
                $request,
                'header_logo',
                'header_logo_path',
                'frontend/images/home-logo.png',
                'upload/header-logo',
                'header-logo-'
            );

            SiteSetting::setValue('footer_description', $validated['footer_description']);

            $this->updateLogoSetting(
                $request,
                'footer_logo',
                'footer_logo_path',
                'frontend/images/home-logo.png',
                'upload/footer-logo',
                'footer-logo-'
            );

            SiteSetting::setValue('contact_phone', $validated['contact_phone']);
            SiteSetting::setValue('contact_email', $validated['contact_email']);
            SiteSetting::setValue('contact_address', $validated['contact_address']);
            SiteSetting::setValue('site_title', $validated['site_title']);

            if ($section === 'general') {
                return back()->with('status', 'General site settings updated successfully.');
            }
        }

        if ($section === 'banners' || $section === 'all') {
            $validated = $request->validate([
                'home_banner'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'gallery_banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'charity_banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'league_banner'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'league_details_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'mentors_banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'coaches_banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'mentor_profile_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'coach_profile_banner'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'privacy_banner'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'terms_banner'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ]);

            $bannerConfigs = [
                ['input' => 'home_banner',           'setting' => 'home_banner_path',           'prefix' => 'home-banner-'],
                ['input' => 'gallery_banner',        'setting' => 'gallery_banner_path',        'prefix' => 'gallery-banner-'],
                ['input' => 'charity_banner',        'setting' => 'charity_banner_path',        'prefix' => 'charity-banner-'],
                ['input' => 'league_banner',         'setting' => 'league_banner_path',         'prefix' => 'league-banner-'],
                ['input' => 'league_details_banner', 'setting' => 'league_details_banner_path', 'prefix' => 'league-details-banner-'],
                ['input' => 'mentors_banner',        'setting' => 'mentors_banner_path',        'prefix' => 'mentors-banner-'],
                ['input' => 'coaches_banner',        'setting' => 'coaches_banner_path',        'prefix' => 'coaches-banner-'],
                ['input' => 'mentor_profile_banner', 'setting' => 'mentor_profile_banner_path', 'prefix' => 'mentor-profile-banner-'],
                ['input' => 'coach_profile_banner',  'setting' => 'coach_profile_banner_path',  'prefix' => 'coach-profile-banner-'],
                ['input' => 'privacy_banner',        'setting' => 'privacy_banner_path',        'prefix' => 'privacy-banner-'],
                ['input' => 'terms_banner',          'setting' => 'terms_banner_path',          'prefix' => 'terms-banner-'],
            ];

            foreach ($bannerConfigs as $b) {
                $this->updateLogoSetting(
                    $request,
                    $b['input'],
                    $b['setting'],
                    'frontend/images/hero_tennis_banner.png',
                    'upload/banners',
                    $b['prefix']
                );
            }

            if ($section === 'banners') {
                return back()->with('status', 'Page banners updated successfully.');
            }
        }

        if ($section === 'stripe' || $section === 'all') {
            $validated = $request->validate([
                'stripe_mode'                  => ['required', 'string', 'in:test,live'],
                'stripe_currency'              => ['required', 'string', 'in:USD,EUR,GBP,CAD,AUD,INR'],
                'stripe_test_publishable_key'  => ['nullable', 'string', 'max:255'],
                'stripe_test_secret_key'       => ['nullable', 'string', 'max:255'],
                'stripe_live_publishable_key'  => ['nullable', 'string', 'max:255'],
                'stripe_live_secret_key'       => ['nullable', 'string', 'max:255'],
            ]);

            SiteSetting::setValue('stripe_mode', $validated['stripe_mode']);
            SiteSetting::setValue('stripe_currency', $validated['stripe_currency']);
            SiteSetting::setValue('stripe_test_publishable_key', $validated['stripe_test_publishable_key']);
            SiteSetting::setValue('stripe_test_secret_key', $validated['stripe_test_secret_key']);
            SiteSetting::setValue('stripe_live_publishable_key', $validated['stripe_live_publishable_key']);
            SiteSetting::setValue('stripe_live_secret_key', $validated['stripe_live_secret_key']);

            if ($section === 'stripe') {
                return back()->with('status', 'Stripe gateway configuration updated successfully.');
            }
        }

        if ($section === 'smtp' || $section === 'all') {
            $validated = $request->validate([
                'smtp_mailer'                  => ['required', 'string', 'in:smtp,log'],
                'smtp_host'                    => ['nullable', 'string', 'max:255'],
                'smtp_port'                    => ['nullable', 'integer', 'min:1', 'max:65535'],
                'smtp_encryption'              => ['required', 'string', 'in:tls,ssl'],
                'smtp_username'                => ['nullable', 'string', 'max:255'],
                'smtp_password'                => ['nullable', 'string', 'max:255'],
                'smtp_from_address'            => ['required', 'email', 'max:255'],
                'smtp_from_name'               => ['required', 'string', 'max:255'],
            ]);

            SiteSetting::setValue('smtp_mailer',       $validated['smtp_mailer']);
            SiteSetting::setValue('smtp_host',         $validated['smtp_host'] ?? '');
            SiteSetting::setValue('smtp_port',         (string) ($validated['smtp_port'] ?? ''));
            SiteSetting::setValue('smtp_encryption',   $validated['smtp_encryption'] ?? 'null');
            SiteSetting::setValue('smtp_username',     $validated['smtp_username'] ?? '');
            if ($request->filled('smtp_password')) {
                SiteSetting::setValue('smtp_password', $validated['smtp_password']);
            }
            SiteSetting::setValue('smtp_from_address', $validated['smtp_from_address']);
            SiteSetting::setValue('smtp_from_name',    $validated['smtp_from_name']);

            if ($section === 'smtp') {
                return back()->with('status', 'SMTP configuration updated successfully.');
            }
        }

        if ($section === 'commission' || $section === 'all') {
            $validated = $request->validate([
                'mentor_commission_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
                'coach_commission_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            ]);

            SiteSetting::setValue('mentor_commission_percent', (string) $validated['mentor_commission_percent']);
            SiteSetting::setValue('coach_commission_percent', (string) $validated['coach_commission_percent']);

            if ($section === 'commission') {
                return back()->with('status', 'Commission settings updated successfully.');
            }
        }

        return back()->with('status', 'Site settings updated successfully.');
    }

    public function testSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $recipient = $validated['test_email'];
        $appName = SiteSetting::siteTitle();

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Hello,\n\nThis is a test email sent from your admin dashboard at {$appName} to verify your SMTP configuration.\n\nIf you received this message, your email server settings are configured correctly!",
                function ($message) use ($recipient, $appName) {
                    $message->to($recipient)
                        ->subject("SMTP Test Email - {$appName}");
                }
            );

            return back()->with('status', "Test email sent successfully to {$recipient}. Please check your inbox / spam folder.");
        } catch (\Throwable $e) {
            return back()->withErrors([
                'smtp_test' => 'Failed to send test email: ' . $e->getMessage(),
            ]);
        }
    }

    protected function updateLogoSetting(
        Request $request,
        string $inputName,
        string $settingKey,
        string $defaultPath,
        string $uploadDirectory,
        string $filenamePrefix
    ): void {
        if (! $request->hasFile($inputName)) {
            return;
        }

        $currentLogo = SiteSetting::getValue($settingKey, $defaultPath);
        $newLogoPath = $this->storeUploadedLogo($request->file($inputName), $uploadDirectory, $filenamePrefix);

        if ($newLogoPath !== null) {
            $this->deleteUploadedLogo($currentLogo, $uploadDirectory.'/');
            SiteSetting::setValue($settingKey, $newLogoPath);
        }
    }

    protected function storeUploadedLogo(UploadedFile $file, string $uploadDirectory, string $filenamePrefix): ?string
    {
        $directory = public_path($uploadDirectory);
        File::ensureDirectoryExists($directory);

        $filename = $filenamePrefix.bin2hex(random_bytes(6)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($directory, $filename);

        return $uploadDirectory.'/'.$filename;
    }

    protected function deleteUploadedLogo(?string $path, string $uploadPrefix): void
    {
        if ($path === null || $path === '' || ! str_starts_with($path, $uploadPrefix)) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
