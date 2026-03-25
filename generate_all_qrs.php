<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Merchant;
use App\Services\QrCodeService;

$service = app(QrCodeService::class);
$merchants = Merchant::where('kyc_status', 'approved')->get();

foreach ($merchants as $m) {
    echo "Generating QR for Merchant ID: {$m->id}... ";
    $path = $service->generateForMerchant($m);
    echo "Done. Path: $path\n";
}
echo "Finished.\n";
