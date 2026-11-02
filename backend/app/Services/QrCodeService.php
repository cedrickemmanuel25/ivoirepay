<?php

namespace App\Services;

use App\Models\Merchant;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate a static QR code PNG for a merchant and persist it to storage.
     * Returns the relative storage path (relative to "public/" disk).
     */
    public function generateForMerchant(Merchant $merchant): string
    {
        $payload = json_encode([
            'merchant_id'   => $merchant->id,
            'business_name' => $merchant->business_name,
            'type'          => 'static_qr',
        ]);

        // Using SVG as it doesn't require the imagick extension on Windows/PHP
        $qr = QrCode::format('svg')->size(400)->errorCorrection('H')->generate($payload);

        $path = 'qrcodes/merchant_' . $merchant->id . '.svg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $qr);

        // Update the merchant record with the QR path
        $merchant->update(['qr_code_path' => $path]);

        return $path;
    }

    /**
     * Return the full public URL for a merchant's QR code.
     */
    public function qrUrl(Merchant $merchant): string
    {
        return Storage::url($merchant->qr_code_path);
    }
}
