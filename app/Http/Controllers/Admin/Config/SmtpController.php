<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmtpController extends Controller
{
    protected $settings;

    public function __construct()
    {
        $this->settings = Setting::getCache();
    }

    public function index(): View
    {
        return view('panel.admin.config.smtp');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with(['message' => __('This feature is disabled in Demo version.'), 'type' => 'error']);
        }

        $data = $request->validate([
            'smtp_host'        => ['nullable', 'string', 'max:255'],
            // A host is useless without a port and a sender, so those become
            // mandatory as soon as the admin starts configuring SMTP.
            'smtp_port'        => ['nullable', 'required_with:smtp_host', 'integer', 'between:1,65535'],
            'smtp_username'    => ['nullable', 'string', 'max:255'],
            'smtp_password'    => ['nullable', 'string', 'max:255'],
            'smtp_email'       => ['nullable', 'required_with:smtp_host', 'email:rfc', 'max:255'],
            'smtp_sender_name' => ['nullable', 'string', 'max:255'],
            'smtp_encryption'  => ['nullable', 'string', Rule::in(['tls', 'ssl', 'TLS', 'SSL'])],
        ], [
            'smtp_port.required_with'  => __('SMTP Port is required when a host is set.'),
            'smtp_email.required_with' => __('SMTP Sender Email is required when a host is set.'),
            'smtp_encryption.in'       => __('SMTP Encryption must be tls or ssl.'),
        ]);

        // Laravel matches the encryption value case sensitively when choosing
        // the smtps scheme, so store it normalised.
        $data['smtp_encryption'] = filled($data['smtp_encryption'] ?? null)
            ? strtolower($data['smtp_encryption'])
            : null;

        // A blank password means "leave it unchanged" rather than "erase the
        // working credentials".
        if (blank($data['smtp_password'] ?? null)) {
            unset($data['smtp_password']);
        }

        $this->settings->update($data);

        Setting::forgetCache();

        Helper::octaneReload();

        return back()->with(['message' => 'Updated Successfully.', 'type' => 'success']);
    }

    public function test(Request $request): string|RedirectResponse
    {
        $toEmail = $request->get('test_email');
        $toName = 'Test Email';

        try {
            Mail::raw('Test email content', function ($message) use ($toEmail, $toName) {
                $message->to($toEmail, $toName)
                    ->subject('Test Email');
            });

            return back()->with(['message' => 'Test email sent!', 'type' => 'success']);

        } catch (Exception $exception) {
            return back()->with(['message' => $exception->getMessage(), 'type' => 'error']);
        }
    }
}
