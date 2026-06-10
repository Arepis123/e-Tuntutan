<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimActivityNotification extends Notification
{
    /**
     * @param Claim  $claim
     * @param string $action  Short label of what the employer did (e.g. "uploaded a document").
     * @param string|null $detail Optional extra context (e.g. document name).
     */
    public function __construct(
        public readonly Claim $claim,
        public readonly string $action,
        public readonly ?string $detail = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $claim   = $this->claim;
        $subject = "[e-Tuntutan] Employer Activity: {$claim->claim_number}";

        $name  = $notifiable->name ?? 'Sir/Madam';
        $email = $notifiable->email ?? $notifiable->routeNotificationFor('mail');

        ClaimEmailLog::record($claim, $email, $name, $subject, "Employer {$this->action}");

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$name},")
            ->line("The employer has {$this->action} on a claim and it requires your attention.")
            ->line("**Claim No.:** {$claim->claim_number}")
            ->line("**Worker:** {$claim->worker->name} ({$claim->worker->passport_number})")
            ->line("**Submitted by:** {$claim->user?->name}");

        if ($this->detail) {
            $mail->line("**Details:** {$this->detail}");
        }

        return $mail
            ->action('View Claim', route('claims.show', $claim))
            ->line('Please review and take the necessary action.')
            ->salutation('Thank you, e-Tuntutan CLAB');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'claim_id'     => $this->claim->id,
            'claim_number' => $this->claim->claim_number,
            'action'       => $this->action,
        ];
    }
}
