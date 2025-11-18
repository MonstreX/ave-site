<?php

namespace Monstrex\AveSite\Validators;

use Illuminate\Support\Facades\Http;

class ReCaptcha
{
    public function validate($attribute, $value, $parameters, $validator): bool
    {
        if (empty($value)) {
            return false;
        }

        $secret = site_setting('general.site_captcha_secret_key');
        if (empty($secret)) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (!$response->successful()) {
            return false;
        }

        $body = $response->json();

        return (bool)($body['success'] ?? false);
    }
}
