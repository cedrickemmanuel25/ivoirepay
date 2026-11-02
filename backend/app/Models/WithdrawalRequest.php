<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'merchant_id',
        'amount',
        'wallet_number',
        'wallet_type',
        'status',
        'provider_ref',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
