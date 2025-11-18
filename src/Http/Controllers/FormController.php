<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Monstrex\AveSite\Notifications\SendFormNotification;
use Monstrex\AveSite\Services\BlockService;

class FormController extends Controller
{
    public function send(Request $request, BlockService $blockService)
    {
        $messageType = 'unknown';
        $messages = [];

        $formAlias = $request->input('_form_alias');
        if (!$formAlias) {
            return $this->respond($request, 'error', [trans('ave-site::forms.missing_form')]);
        }

        $form = $blockService->getFormByKey($formAlias);
        if (!$form) {
            return $this->respond($request, 'error', [trans('ave-site::forms.missing_form')]);
        }

        $options = json_decode($form->details ?? '') ?: (object)[];
        $validator = null;

        if (isset($options->validator)) {
            $validatorMessages = isset($options->messages) ? (array)$options->messages : [];
            $validator = Validator::make($request->all(), (array)$options->validator, $validatorMessages);

            if (!$validator->passes()) {
                $messageType = 'error-validation';
                $messages = $validator->errors()->all();

                return $this->respond($request, $messageType, $messages, $validator);
            }
        }

        $formFields = $request->except(['_token', '_form_alias', '_mail_to', 'g-recaptcha-response']);

        try {
            $toAddress = str_replace(' ', '', $options->to_address ?? site_setting('mail.to_address'));

            if ($request->filled('_mail_to')) {
                $toAddress = site_setting($request->input('_mail_to'), $toAddress);
            }

            $emails = collect(explode(',', (string)$toAddress))
                ->map(fn ($email) => trim($email))
                ->filter()
                ->values()
                ->all();

            if (empty($emails)) {
                throw new \RuntimeException(trans('ave-site::forms.send_error'));
            }

            Notification::route('mail', $emails)->notify(new SendFormNotification($formFields, $request));

            $messageType = 'success';
            $messages[] = trans('ave-site::forms.send_success');
        } catch (\Throwable $e) {
            $messageType = 'error';
            $messages[] = $e->getMessage();
            Log::error('ave-site.form.send_failed', ['exception' => $e]);
        }

        return $this->respond($request, $messageType, $messages, $validator);
    }

    protected function respond(Request $request, string $type, array $messages, $validator = null)
    {
        if ($request->ajax()) {
            $messagesHtml = '<ul>';
            foreach ($messages as $message) {
                $messagesHtml .= '<li>' . e($message) . '</li>';
            }
            $messagesHtml .= '</ul>';

            return response()->json([
                'type' => $type,
                'messages' => $messagesHtml,
            ], $type === 'success' ? 200 : 422);
        }

        $response = redirect()->back()->withInput();

        if ($validator instanceof \Illuminate\Contracts\Support\MessageBag || $validator instanceof \Illuminate\Validation\Validator) {
            $response = $response->withErrors($validator);
        } elseif ($type === 'error-validation') {
            $bag = new MessageBag();
            foreach ($messages as $message) {
                $bag->add('form', $message);
            }
            $response = $response->withErrors($bag);
        }

        return $response->with([
            'form_status' => $type,
            'form_messages' => $messages,
        ]);
    }
}
