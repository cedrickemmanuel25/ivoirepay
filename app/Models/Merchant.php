<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Merchant extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_address',
        'rccm_number',
        'cni_number',
        'balance',
        'qr_code_path',
        'kyc_status',
        'kyc_rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function getQrCodeUrlAttribute()
    {
        return Storage::url($this->qr_code_path);
    }
}
