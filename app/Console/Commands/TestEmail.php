<?php

namespace App\Console\Commands;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimSubmittedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class TestEmail extends Command
{
    protected $signature = 'mail:test {to?} {--claim= : Test PIC notification for a claim ID}';
    protected $description = 'Send a test email to verify SMTP configuration';

    public function handle(): int
    {
        if ($claimId = $this->option('claim')) {
            return $this->testClaimNotification((int) $claimId);
        }

        $to = $this->argument('to') ?? 'arepis123@gmail.com';

        $this->info("Sending test email to: {$to}");
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('From: ' . config('mail.from.address'));

        try {
            Mail::raw('Test email from e-Tuntutan system. SMTP is working correctly.', function ($m) use ($to) {
                $m->to($to)->subject('[e-Tuntutan] SMTP Test - ' . now()->format('d/m/Y H:i:s'));
            });
            $this->info('SUCCESS: Email sent!');
        } catch (\Exception $e) {
            $this->error('FAILED: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testClaimNotification(int $claimId): int
    {
        $claim = Claim::with(['worker', 'user'])->find($claimId);
        if (! $claim) {
            $this->error("Claim #{$claimId} not found.");
            return 1;
        }

        $pics = User::role('pic')->where('notify_on_submission', true)->get();
        $this->info("Found {$pics->count()} PIC(s) to notify:");
        foreach ($pics as $pic) {
            $this->line("  - {$pic->name} <{$pic->email}>");
        }

        if ($pics->isEmpty()) {
            $this->warn('No PICs found. Check that users have the "pic" role and notify_on_submission = true.');
            return 1;
        }

        $this->info("Sending ClaimSubmittedNotification for claim {$claim->claim_number}...");

        try {
            Notification::send($pics, new ClaimSubmittedNotification($claim));
            $this->info('SUCCESS: Notification sent to all PICs!');
        } catch (\Exception $e) {
            $this->error('FAILED: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
