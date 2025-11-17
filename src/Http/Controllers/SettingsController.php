<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;
use Monstrex\Ave\Media\Facades\Media as MediaFacade;
use Monstrex\AveSite\Models\Setting;
use Monstrex\AveSite\Notifications\TestMailNotification;

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

        $settings->load('media');
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

        $removeField = $request->has('remove_image')
            ? trim($request->remove_image)
            : ($request->has('remove_media') ? trim($request->remove_media) : null);

        if ($removeField) {
            foreach ($config->fields as $key_field => $field) {
                if ($key_field === $removeField) {
                    $settings->clearMediaCollection($key_field);
                    $config->fields->{$key_field}->value = '';
                }
            }
        } else {
            foreach ($config->fields as $key_field => $field) {
                if (in_array($field->type, ['text', 'number', 'textarea', 'code_editor', 'rich_text_box'])) {
                    $config->fields->{$key_field}->value = $request->{$key_field} ?? '';
                } elseif ($field->type === 'checkbox') {
                    $config->fields->{$key_field}->value = isset($request->{$key_field}) ? '1' : '0';
                } elseif (in_array($field->type, ['radio', 'dropdown'])) {
                    $config->fields->{$key_field}->value = $request->{$key_field} ?? '';
                } elseif (in_array($field->type, ['media', 'image'])) {
                    if ($request->hasFile($key_field)) {
                        $settings->clearMediaCollection($key_field);

                        $media = MediaFacade::add($request->file($key_field))
                            ->model($settings)
                            ->collection($key_field)
                            ->pathPrefix('settings/' . $key)
                            ->create()
                            ->first();

                        $config->fields->{$key_field}->value = $media?->id ?? '';
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
