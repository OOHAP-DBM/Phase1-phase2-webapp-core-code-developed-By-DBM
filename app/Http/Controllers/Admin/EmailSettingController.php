<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailSettingController extends Controller
{

    public function index()
    {
        $emailSetting = EmailSetting::first();

        return view('admin.email_settings.index', compact('emailSetting'));
    }


    public function update(Request $request)
    {
        $validated = $request->validate([
            'mailer' => [
                'required',
                'string',
                'max:50',
            ],

            'host' => [
                'required',
                'string',
                'max:255',
            ],

            'port' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],

            'encryption' => [
                'nullable',
                'in:tls,ssl,null',
            ],

            'username' => [
                'nullable',
                'string',
                'max:255',
            ],

            'password' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'from_name' => [
                'required',
                'string',
                'max:255',
            ],

            'from_email' => [
                'required',
                'email',
                'max:255',
            ],

            'reply_to_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reply_to_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $emailSetting = EmailSetting::first();

        if (!$emailSetting) {
            $emailSetting = new EmailSetting();
        }

        if (!$request->filled('password')) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = auth()->id();

        $emailSetting->fill($validated);
        $emailSetting->save();

        return redirect()
            ->route('email_settings.index')
            ->with('success', 'Email settings updated successfully.');
    }


    public function test(Request $request)
    {
        $request->validate([
            'test_email' => [
                'required',
                'email',
            ],
        ]);

        $setting = EmailSetting::first();

        if (!$setting) {
            return back()->with('error', 'Please configure email settings first.');
        }

        if (!$setting->is_active) {
            return back()->with('error', 'Email configuration is currently inactive.');
        }

        try {
            $this->configureMailer($setting);

            Mail::raw(
                'This is a test email from OOHAPP Email Settings.',
                function ($message) use ($setting, $request) {
                    $message
                        ->to($request->test_email)
                        ->subject('OOHAPP - Test Email');

                    if ($setting->from_email) {
                        $message->from(
                            $setting->from_email,
                            $setting->from_name
                        );
                    }

                    if ($setting->reply_to_email) {
                        $message->replyTo(
                            $setting->reply_to_email,
                            $setting->reply_to_name
                        );
                    }
                }
            );

            return back()->with(
                'success',
                'Test email sent successfully.'
            );

        } catch (Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Test email failed. Please check your SMTP configuration.'
            );
        }
    }

    private function configureMailer(EmailSetting $setting): void
    {
        Config::set('mail.default', $setting->mailer);

        Config::set(
            'mail.mailers.smtp.host',
            $setting->host
        );

        Config::set(
            'mail.mailers.smtp.port',
            $setting->port
        );
    
        Config::set(
            'mail.mailers.smtp.encryption',
            $setting->encryption === 'null'
            ? null
            : $setting->encryption
        );

        Config::set(
            'mail.mailers.smtp.username',
            $setting->username
        );

        Config::set(
            'mail.mailers.smtp.password',
            $setting->password
        );

        Config::set(
            'mail.from.address',
            $setting->from_email
        );

        Config::set(
            'mail.from.name',
            $setting->from_name
        );
    }
}