<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Http\Request;
use Monstrex\AveSite\Models\Setting;
use Monstrex\AveSite\Notifications\TestMailNotification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Require authentication for all settings routes
        $this->middleware('auth');
    }

    /**
     * Show settings edit form
     */
    public function edit($key)
    {
        $settings = Setting::where('key', $key)->first();

        if (!$settings) {
            abort(404, "Settings group '{$key}' not found");
        }

        $config = json_decode($settings->fields);

        return view('ave-site::settings.index', [
            'key' => $key,
            'title' => $settings->title,
            'config' => $config,
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request, $key)
    {
        $settings = Setting::where('key', $key)->first();

        if (!$settings) {
            abort(404, "Settings group '{$key}' not found");
        }

        $config = json_decode($settings->fields);

        // Handle image removal
        if ($request->has('remove_image')) {
            foreach ($config->fields as $key_field => $field) {
                if ($key_field === trim($request->remove_image)) {
                    $config->fields->{$key_field}->value = '';
                }
            }
        }
        // Handle media removal
        elseif ($request->has('remove_media')) {
            foreach ($config->fields as $key_field => $field) {
                if ($key_field === trim($request->remove_media)) {
                    // Delete file if it exists
                    if (!empty($field->value)) {
                        $filePath = public_path($field->value);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    $config->fields->{$key_field}->value = '';
                }
            }
        }
        // Save all data from request
        else {
            foreach ($config->fields as $key_field => $field) {
                if ($field->type === 'text' || $field->type === 'number' || $field->type === 'textarea' || $field->type === 'code_editor' || $field->type === 'rich_text_box') {
                    $config->fields->{$key_field}->value = $request->{$key_field} ?? '';
                } elseif ($field->type === 'checkbox') {
                    $config->fields->{$key_field}->value = isset($request->{$key_field}) ? '1' : '0';
                } elseif ($field->type === 'radio' || $field->type === 'dropdown') {
                    $config->fields->{$key_field}->value = $request->{$key_field} ?? '';
                } elseif ($field->type === 'media') {
                    if ($request->hasFile($key_field)) {
                        $file = $request->file($key_field);
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storePublicly('settings/' . $key, 'public');
                        $config->fields->{$key_field}->value = '/storage/' . $path;
                    }
                }
            }
        }

        $settings->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $settings->save();

        return back()->with('success', trans('ave-site::resources_settings.messages.updated_successfully', ['title' => $settings->title]));
    }

    /**
     * Send test email
     * Based on filament-site implementation
     */
    public function testMail(Request $request)
    {
        try {
            $toAddress = site_setting('mail.to_address');

            if (!$toAddress) {
                return view('ave-site::settings.mail-sent')->with([
                    'title' => trans('ave-site::notifications.test_mail_title'),
                    'content' => null,
                    'error' => trans('ave-site::resources_settings.messages.to_address_not_configured')
                ]);
            }

            // Handle multiple recipients separated by comma
            $emails = collect(explode(',', $toAddress))
                ->map(fn ($address) => trim($address))
                ->filter()
                ->values()
                ->all();

            if (empty($emails)) {
                return view('ave-site::settings.mail-sent')->with([
                    'title' => trans('ave-site::notifications.test_mail_title'),
                    'content' => null,
                    'error' => trans('ave-site::resources_settings.messages.to_address_not_configured')
                ]);
            }

            Notification::route('mail', $emails)->notify(new TestMailNotification());

            return view('ave-site::settings.mail-sent')->with([
                'title' => trans('ave-site::notifications.test_mail_title'),
                'content' => trans('ave-site::notifications.test_mail_success_message'),
                'error' => null
            ]);
        } catch (\Exception $e) {
            return view('ave-site::settings.mail-sent')->with([
                'title' => trans('ave-site::notifications.test_mail_title'),
                'content' => null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
