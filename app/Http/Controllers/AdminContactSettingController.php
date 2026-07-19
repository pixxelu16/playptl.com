<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
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
            'stripe'            => SiteSetting::stripe(),
            'mentorCommission'  => SiteSetting::mentorCommissionPercent(),
            'coachCommission'   => SiteSetting::coachCommissionPercent(),
            'smtp'              => SiteSetting::smtp(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'header_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'footer_description' => ['required', 'string', 'max:1000'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_address' => ['required', 'string', 'max:500'],
            'stripe_mode'                  => ['required', 'string', 'in:test,live'],
            'stripe_currency'              => ['required', 'string', 'in:USD,EUR,GBP,CAD,AUD,INR'],
            'stripe_test_publishable_key'  => ['nullable', 'string', 'max:255'],
            'stripe_test_secret_key'       => ['nullable', 'string', 'max:255'],
            'stripe_live_publishable_key'  => ['nullable', 'string', 'max:255'],
            'stripe_live_secret_key'       => ['nullable', 'string', 'max:255'],
            'mentor_commission_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'coach_commission_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            // SMTP
            'smtp_mailer'                  => ['required', 'string', 'in:smtp,log'],
            'smtp_host'                    => ['nullable', 'string', 'max:255'],
            'smtp_port'                    => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption'              => ['required', 'string', 'in:tls,ssl'],
            'smtp_username'                => ['nullable', 'string', 'max:255'],
            'smtp_password'                => ['nullable', 'string', 'max:255'],
            'smtp_from_address'            => ['required', 'email', 'max:255'],
            'smtp_from_name'               => ['required', 'string', 'max:255'],
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

        SiteSetting::setValue('stripe_mode', $validated['stripe_mode']);
        SiteSetting::setValue('stripe_currency', $validated['stripe_currency']);
        SiteSetting::setValue('stripe_test_publishable_key', $validated['stripe_test_publishable_key']);
        if ($request->filled('stripe_test_secret_key')) {
            SiteSetting::setValue('stripe_test_secret_key', $validated['stripe_test_secret_key']);
        }
        SiteSetting::setValue('stripe_live_publishable_key', $validated['stripe_live_publishable_key']);
        if ($request->filled('stripe_live_secret_key')) {
            SiteSetting::setValue('stripe_live_secret_key', $validated['stripe_live_secret_key']);
        }
        SiteSetting::setValue('mentor_commission_percent', (string) $validated['mentor_commission_percent']);
        SiteSetting::setValue('coach_commission_percent', (string) $validated['coach_commission_percent']);

        // SMTP
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

        return back()->with('status', 'Site settings updated successfully.');
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
