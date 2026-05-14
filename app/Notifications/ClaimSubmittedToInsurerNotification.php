<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimSubmittedToInsurerNotification extends Notification
{

    public function __construct(public readonly Claim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim    = $this->claim;
        $picEmail = $claim->company_pic_email;
        $picName  = $claim->company_pic_name ?: $notifiable->name;
        $subject  = "[e-Tuntutan] Application Submitted to Insurer: {$claim->claim_number}";

        ClaimEmailLog::record($claim, $picEmail ?: $notifiable->email, $picName, $subject, 'Submitted to Insurer');

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$picName},")
            ->line("We would like to inform you that your claim application has been submitted to the insurance provider (Liberty) for processing.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("**Claim Type:** {$claim->getClaimTypeLabel()}")
            ->line("**Submitted On:** {$claim->submitted_to_insurer_at->format('d/m/Y')}")
            ->line("We will notify you once there is an update from the insurer.")
            ->action('View Claim', route('claims.show', $claim))
            ->salutation('Thank you, e-Tuntutan CLAB');

        return $mail;
    }
}
