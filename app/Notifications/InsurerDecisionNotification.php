<?php

namespace App\Notifications;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InsurerDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Claim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim    = $this->claim;
        $approved = $claim->insurer_decision === 'approved';
        $picEmail = $claim->company_pic_email;
        $picName  = $claim->company_pic_name ?: $notifiable->name;

        $mail = (new MailMessage)
            ->subject("[e-Tuntutan] Insurer Decision: {$claim->claim_number} — " . ($approved ? 'Approved' : 'Not Approved'))
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

        if ($picEmail) {
            $mail->to($picEmail, $picName);
        }

        return $mail;
    }
}
