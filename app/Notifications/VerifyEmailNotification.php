<?php

namespace App\Notifications;

use App\Models\EmailOtp;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $otp = rand(100000, 999999);

        EmailOtp::query()->updateOrCreate(
            ['email' => $notifiable->email],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(5)]
        );

        return (new MailMessage)
            ->subject('Verify Your Email with OTP')
            ->greeting('Hello!')
            ->line('Your verification code is:')
            ->line("🔐 **$otp**")
            ->line('This code will expire in 5 minutes.')
            ->line('If you did not request this, you can ignore this email.')
            ->salutation("Regards, \nTerri Berry Group");
    }
}
