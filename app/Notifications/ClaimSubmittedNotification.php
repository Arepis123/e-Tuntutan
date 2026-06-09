<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimSubmittedNotification extends Notification
{

    public function __construct(public readonly Claim $claim)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim   = $this->claim;
        $subject = "[e-Tuntutan] New Claim: {$claim->claim_number}";

        // User notifiables expose email/name; anonymous (routed) ones do not.
        $email = $notifiable->email ?? $notifiable->routeNotificationFor('mail') ?? $claim->company_pic_email;
        $name  = $notifiable->name ?? $claim->company_pic_name ?? 'Sir/Madam';

        ClaimEmailLog::record($claim, $email, $name, $subject, 'Claim Submitted');

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$name},")
            ->line("A new claim has been submitted and requires your attention.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Type:** {$claim->getClaimTypeLabel()}")
            ->line("**Category:** " . ($claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death'))
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("**Submitted by:** {$claim->user->name}")
            ->line("**Incident Date:** " . $claim->incident_date?->format('d/m/Y'))
            ->action('View Claim', route('claims.show', $claim))
            ->line('Please review and take the necessary action.')
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
