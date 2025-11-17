<?php

namespace Monstrex\AveSite\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TestMailNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('ave-site::notifications.test_mail_subject'))
            ->greeting(trans('ave-site::notifications.greeting'))
            ->line(trans('ave-site::notifications.test_mail_message'))
            ->salutation(trans('ave-site::notifications.salutation'));
    }
}
