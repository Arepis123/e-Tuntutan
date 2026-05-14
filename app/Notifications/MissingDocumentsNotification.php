<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\ClaimEmailLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissingDocumentsNotification extends Notification
{

    public function __construct(
        public readonly Claim $claim,
        public readonly string $subject,
        public readonly string $body,
        public readonly array $cc = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $picEmail = $this->claim->company_pic_email;
        $picName  = $this->claim->company_pic_name ?: $notifiable->name;

        ClaimEmailLog::record($this->claim, $picEmail ?: $notifiable->email, $picName, $this->subject, 'Custom Email');

        $mail = (new MailMessage)->subject($this->subject);

        if ($this->cc) {
            $mail->cc($this->cc);
        }

        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $this->body)));

        foreach ($paragraphs as $paragraph) {
            $mail->line(str_replace("\n", ' ', $paragraph));
        }

        return $mail
            ->action('View Claim', route('claims.show', $this->claim))
            ->salutation('Thank you, e-Tuntutan CLAB');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'claim_id'     => $this->claim->id,
            'claim_number' => $this->claim->claim_number,
        ];
    }
}
