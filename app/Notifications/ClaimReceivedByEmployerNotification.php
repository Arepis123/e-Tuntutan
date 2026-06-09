<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimReceivedByEmployerNotification extends Notification
{

    public function __construct(public readonly Claim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim    = $this->claim;
        $picName  = $claim->company_pic_name ?: ($notifiable->name ?? 'Sir/Madam');
        $picEmail = $claim->company_pic_email ?: ($notifiable->email ?? $notifiable->routeNotificationFor('mail'));
        $subject  = "[e-Tuntutan] Claim Received: {$claim->claim_number}";

        ClaimEmailLog::record($claim, $picEmail, $picName, $subject, 'Claim Received (Employer)');

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$picName},")
            ->line("Thank you for submitting your claim. We have received your application and it is currently being reviewed by our team.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Claim Type:** {$claim->getClaimTypeLabel()}")
            ->line("**Category:** " . ($claim->claim_category === 'hospitalization' ? 'Hospitalization' : 'Death'))
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("**Incident Date:** " . $claim->incident_date?->format('d/m/Y'))
            ->line("**Submitted On:** " . $claim->submitted_at?->format('d/m/Y'))
            ->line("Please ensure that all required supporting documents are submitted to CLAB as soon as possible to avoid any delays in processing your claim.")
            ->action('View Claim', route('claims.show', $claim))
            ->line('Should you have any enquiries, please do not hesitate to contact us.')
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
