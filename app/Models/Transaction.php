<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'reference',
        'merchant_id',
        'client_id',
        'amount',
        'wallet_type',
        'status',
        'commission_amount',
        'payment_provider_ref',
        'wallet_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function scopeSuccessful($q)
    {
        return $q->where('status', 'success');
    }

    public function scopeToday($q)
    {
        return $q->whereDate('created_at', today());
    }

    public function scopeThisMonth($q)
    {
        return $q->whereMonth('created_at', now()->month);
    }
}
