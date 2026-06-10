<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'company_name',
        'external_id',
        'notify_on_submission',
    ];

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Staff who should be notified of any employer activity:
     * PICs who have email notifications switched on, plus all admins.
     */
    public static function staffRecipients(): \Illuminate\Support\Collection
    {
        return static::role('pic')->where('notify_on_submission', true)->get()
            ->merge(static::role('admin')->get())
            ->unique('id')
            ->values();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
