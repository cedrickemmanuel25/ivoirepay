<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    protected $fillable = [
        'merchant_id',
        'document_type',
        'file_path',
        'original_name',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
