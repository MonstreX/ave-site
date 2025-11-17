<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Http\Request;
use Monstrex\AveSite\Models\Setting;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
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

        return redirect()->route('ave.resources.index', ['resource' => 'site-settings'])
            ->with('success', "Settings '{$settings->title}' updated successfully");
    }
}
