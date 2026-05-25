<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LibertyCollectionRequestNotification extends Notification
{
    public function __construct(
        public readonly Claim $claim,
        public readonly string $subject,
        public readonly string $body,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        ClaimEmailLog::record(
            $this->claim,
            config('services.liberty.email'),
            config('services.liberty.name', 'Howden Group'),
            $this->subject,
            'Howden Collection Request Sent'
        );

        $mail = (new MailMessage)->subject($this->subject);

        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $this->body)));

        foreach ($paragraphs as $paragraph) {
            $mail->line(str_replace("\n", ' ', $paragraph));
        }

        return $mail->salutation(' ');
    }
}
