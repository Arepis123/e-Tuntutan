<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimNoteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Claim $claim,
        public readonly User $author,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim    = $this->claim;
        $picEmail = $claim->company_pic_email;
        $picName  = $claim->company_pic_name ?: $notifiable->name;

        $mail = (new MailMessage)
            ->subject("[e-Tuntutan] New Message on Claim: {$claim->claim_number}")
            ->greeting("Dear {$picName},")
            ->line("A new message has been posted on claim **{$claim->claim_number}** by **{$this->author->name}**.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("Please log in to read the message and reply if necessary.")
            ->action('View Claim', route('claims.show', $claim))
            ->salutation('Thank you, e-Tuntutan CLAB');

        if ($picEmail) {
            $mail->to($picEmail, $picName);
        }

        return $mail;
    }
}
