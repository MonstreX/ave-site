<?php

namespace Monstrex\AveSite\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SendFormNotification extends Notification
{
    use Queueable;

    protected array $formFields;
    protected Request $request;

    public function __construct(array $formFields, Request $request)
    {
        $this->formFields = $formFields;
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->greeting(trans('ave-site::notifications.greeting'));

        if (!empty($this->formFields['subject'])) {
            $mail->subject($this->formFields['subject']);
        }

        foreach ($this->formFields as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $mail->line(new HtmlString('<p><strong>' . e($key) . '</strong>: ' . e($value) . '</p>'));
        }

        $mail->salutation(trans('ave-site::notifications.salutation'));

        $files = $this->request->file();
        foreach ($files as $inputName => $uploadedFiles) {
            $filesArray = is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles];
            foreach ($filesArray as $file) {
                $mail->attach($file->getRealPath(), [
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ]);
            }
        }

        return $mail;
    }
}
