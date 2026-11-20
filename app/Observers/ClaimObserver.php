<?php

namespace App\Observers;

use App\Models\Claim;
use App\Models\ClaimAuditLog;

/**
 * Records every Eloquent change to a claim in claim_audit_logs.
 *
 * Note: raw query-builder updates (DB::table('claims')->update()) and
 * saveQuietly() bypass this — always change claims through the model.
 */
class ClaimObserver
{
    private const IGNORED = ['updated_at', 'created_at'];

    public function created(Claim $claim): void
    {
        ClaimAuditLog::record($claim, 'created', [
            'claim_number' => ['old' => null, 'new' => $claim->claim_number],
            'status'       => ['old' => null, 'new' => $claim->status],
            'user_id'      => ['old' => null, 'new' => $claim->user_id],
        ]);
    }

    public function updated(Claim $claim): void
    {
        $changes = [];

        // In the "updated" event, getChanges() holds the new values and the
        // raw original still holds the pre-update values.
        foreach (array_diff_key($claim->getChanges(), array_flip(self::IGNORED)) as $field => $new) {
            $changes[$field] = ['old' => $claim->getRawOriginal($field), 'new' => $new];
        }

        if ($changes) {
            ClaimAuditLog::record($claim, 'updated', $changes);
        }
    }

    public function deleted(Claim $claim): void
    {
        ClaimAuditLog::record($claim, $claim->isForceDeleting() ? 'force_deleted' : 'deleted');
    }

    public function restored(Claim $claim): void
    {
        ClaimAuditLog::record($claim, 'restored');
    }
}
