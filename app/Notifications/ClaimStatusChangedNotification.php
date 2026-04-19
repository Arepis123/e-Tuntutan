<?php

namespace App\Notifications;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Claim $claim)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim  = $this->claim;
        $status = match ($claim->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'closed'      => 'Closed',
            default       => $claim->status,
        };

        $subject = "[e-Tuntutan] Status Updated: {$claim->claim_number}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$notifiable->name},")
            ->line("The claim status has been updated.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**New Status:** {$status}")
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})");

        if ($claim->rejection_reason) {
            $mail->line("**Rejection Reason:** {$claim->rejection_reason}");
        }

        if ($claim->forwarded_to) {
            $forwarded = strtoupper(str_replace('_', ' ', $claim->forwarded_to));
            $mail->line("**Forwarded To:** {$forwarded}");
        }

        return $mail
            ->action('View Claim', route('claims.show', $claim))
            ->line('Please review and contact the employer/relevant party.')
            ->salutation('Thank you, e-Tuntutan CLAB');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'claim_id'     => $this->claim->id,
            'claim_number' => $this->claim->claim_number,
            'status'       => $this->claim->status,
        ];
    }
}
