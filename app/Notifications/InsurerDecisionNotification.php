<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InsurerDecisionNotification extends Notification
{

    public function __construct(public readonly Claim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim    = $this->claim;
        $approved = $claim->insurer_decision === 'approved';
        $picEmail = $claim->company_pic_email ?: $notifiable->routeNotificationFor('mail');
        $picName  = $claim->company_pic_name ?: ($notifiable->name ?? 'Sir/Madam');
        $subject  = "[e-Tuntutan] Insurer Decision: {$claim->claim_number} — " . ($approved ? 'Approved' : 'Not Approved');

        ClaimEmailLog::record($claim, $picEmail, $picName, $subject, 'Insurer Decision: ' . ($approved ? 'Approved' : 'Not Approved'));

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$picName},")
            ->line("We have received a decision from the insurance provider (Liberty) regarding your claim application.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("**Decision:** " . ($approved ? '✅ Approved' : '❌ Not Approved'));

        if ($approved) {
            $mail->line("An approval letter has been attached to your claim record. Please log in to view the details.");
        } else {
            $mail->line("**Reason:** {$claim->insurer_rejection_reason}");
            $mail->line("Please contact our office if you have any questions or wish to appeal.");
        }

        $mail->action('View Claim', route('claims.show', $claim))
             ->salutation('Thank you, e-Tuntutan CLAB');

        return $mail;
    }
}
