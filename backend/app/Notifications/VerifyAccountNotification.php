<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class VerifyAccountNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return env('MAIL_PASSWORD') ? ['mail'] : ['mail', 'log'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(24),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Welcome to Earthbred - Verify Your Account')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An account has been created for you at Earthbred Coffee Studio POS as a ' . ucfirst($notifiable->role) . '.')
            ->line('Please click the button below to verify your email address and activate your account.')
            ->action('Verify & Activate Account', $verificationUrl)
            ->line('If you did not request this account, no further action is required.');
    }
}
