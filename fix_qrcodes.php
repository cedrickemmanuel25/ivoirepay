<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Merchant;
use Illuminate\Support\Facades\Storage;

$merchants = Merchant::all();
foreach ($merchants as $m) {
    echo "Merchant ID: {$m->id}, Name: {$m->business_name}\n";
    echo "  Current path: {$m->qr_code_path}\n";
    
    $exists = Storage::disk('public')->exists($m->qr_code_path);
    echo "  Exists on public disk: " . ($exists ? "YES" : "NO") . "\n";
    
    if (!$exists) {
        echo "  Clearing path to force regeneration...\n";
        $m->update(['qr_code_path' => null]);
    }
}
