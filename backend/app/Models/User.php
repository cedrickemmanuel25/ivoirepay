<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'pin_hash',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'pin_hash',
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
            'pin_hash' => 'hashed',
        ];
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->pin_hash;
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    /**
     * Expose kyc_status from related merchant for the /api/user endpoint.
     * SplashScreen uses this to route merchants correctly after login.
     */
    public function getKycStatusAttribute(): ?string
    {
        return $this->merchant?->kyc_status;
    }

    /**
     * Returns true if merchant has already set up their PIN.
     * Used by SplashScreen to route approved merchants to /setup-pin or /pin-login.
     */
    public function getHasPinAttribute(): bool
    {
        return !is_null($this->getRawOriginal('pin_hash'));
    }

    protected $appends = ['kyc_status', 'has_pin'];

    public function clientTransactions()
    {
        return $this->hasMany(Transaction::class, 'client_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
