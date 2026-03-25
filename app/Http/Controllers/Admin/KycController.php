<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\AfricasTalkingService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        
        $query = Merchant::with('user');
        
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('kyc_status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('rccm_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $merchants = $query->latest('created_at')->paginate(25);
        $pendingKycCount = Merchant::where('kyc_status', 'pending')->count();
        $unreadNotifications = 0; // You can replace this later with actual notification logic
        
        return view('admin.kyc.index', compact('merchants', 'status', 'pendingKycCount', 'unreadNotifications'));
    }

    public function show($id)
    {
        $merchant = Merchant::with(['user', 'kycDocuments'])->findOrFail($id);
        $pendingKycCount = Merchant::where('kyc_status', 'pending')->count();
        $unreadNotifications = 0;
        
        return view('admin.kyc.show', compact('merchant', 'pendingKycCount', 'unreadNotifications'));
    }

    public function approve(Request $request, $id, AfricasTalkingService $smsService, QrCodeService $qrService)
    {
        $merchant = Merchant::with('user')->findOrFail($id);
        
        if ($merchant->kyc_status !== 'pending') {
            return back()->with('error', 'Ce dossier a déjà été traité.');
        }
        
        $merchant->kyc_status = 'approved';
        $merchant->approved_at = now();
        $merchant->kyc_rejection_reason = null;
        $merchant->save();
        
        // Generate static QR code
        $qrService->generateForMerchant($merchant);
        
        // Send SMS
        if ($merchant->user && $merchant->user->phone) {
            $message = "IvoirePay : Félicitations ! Votre compte commerçant est approuvé.\nVous pouvez maintenant recevoir des paiements via QR Code.";
            $smsService->sendMessage($merchant->user->phone, $message);
        }
        
        return redirect()->route('admin.kyc.index')->with('success', 'Dossier approuvé avec succès.');
    }

    public function reject(Request $request, $id, AfricasTalkingService $smsService)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        $merchant = Merchant::with('user')->findOrFail($id);
        
        if ($merchant->kyc_status !== 'pending') {
            return back()->with('error', 'Ce dossier a déjà été traité.');
        }
        
        $merchant->kyc_status = 'rejected';
        $merchant->kyc_rejection_reason = $request->input('reason');
        $merchant->save();
        
        // Send SMS
        if ($merchant->user && $merchant->user->phone) {
            $message = "IvoirePay : Votre dossier KYC a été rejeté.\nMotif : {$merchant->kyc_rejection_reason}. Contactez support@ivoirepay.ci";
            $smsService->sendMessage($merchant->user->phone, $message);
        }
        
        return redirect()->route('admin.kyc.index')->with('success', 'Dossier rejeté.');
    }
}
