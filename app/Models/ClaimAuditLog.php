<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClaimAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write an audit row for the given claim, capturing who/where from the
     * current request (or console) context.
     */
    public static function record(Claim $claim, string $event, ?array $changes = null): void
    {
        $user      = Auth::user();
        $isConsole = app()->runningInConsole() && ! app()->runningUnitTests();
        $request   = $isConsole ? null : request();

        static::create([
            'claim_id'   => $claim->getKey(),
            'event'      => $event,
            'changes'    => $changes ?: null,
            'user_id'    => $user?->getKey(),
            'user_name'  => $user?->name,
            'user_email' => $user?->email,
            'user_roles' => $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->implode(',') : null,
            'source'     => $isConsole ? 'console' : 'web',
            'action'     => $isConsole ? static::consoleCommand() : static::livewireMethods(),
            'url'        => $request ? Str::limit($request->header('Referer') ?: $request->fullUrl(), 2040, '') : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? Str::limit((string) $request->userAgent(), 500, '') : null,
        ]);
    }

    /** Livewire method(s) invoked in this request, e.g. "confirmReject". */
    protected static function livewireMethods(): ?string
    {
        $methods = collect(request()->input('components', []))
            ->flatMap(fn ($c) => collect($c['calls'] ?? [])->pluck('method'))
            ->filter(fn ($m) => is_string($m))
            ->unique()
            ->implode(',');

        return $methods !== '' ? Str::limit($methods, 250, '') : null;
    }

    protected static function consoleCommand(): ?string
    {
        $argv = $_SERVER['argv'] ?? [];

        return $argv ? Str::limit('artisan '.implode(' ', array_slice($argv, 1)), 250, '') : null;
    }
}
