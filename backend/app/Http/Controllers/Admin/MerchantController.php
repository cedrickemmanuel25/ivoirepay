<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = Merchant::with('user')->latest();

        // Search by name, phone, email, rccm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('rccm_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by KYC status
        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        // Filter by Active status
        if ($request->filled('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('user', function ($uq) use ($isActive) {
                $uq->where('is_active', $isActive);
            });
        }

        $merchants = $query->paginate(25)->withQueryString();

        return view('admin.merchants.index', compact('merchants'));
    }

    public function show($id)
    {
        $merchant = Merchant::with(['user', 'kycDocuments'])->findOrFail($id);
        
        // Fetch 20 latest transactions for this merchant
        $transactions = Transaction::with('client')
            ->where('merchant_id', $merchant->id)
            ->latest()
            ->paginate(20, ['*'], 'tx_page');
            
        // Assuming we calculate total withdrawal amount or something similar for the financial section
        $totalWithdrawals = 0; // Placeholder for now, can be updated if there's a withdrawals table

        return view('admin.merchants.show', compact('merchant', 'transactions', 'totalWithdrawals'));
    }

    public function toggle(Request $request, $id)
    {
        $merchant = Merchant::with('user')->findOrFail($id);
        $user = $merchant->user;
        
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'message' => $user->is_active ? 'Commerçant activé avec succès.' : 'Commerçant désactivé.'
        ]);
    }

    public function downloadQr($id)
    {
        $merchant = Merchant::findOrFail($id);

        if (!$merchant->qr_code_path || !Storage::disk('public')->exists($merchant->qr_code_path)) {
            return back()->with('error', 'Le QR code n\'est pas disponible pour ce commerçant.');
        }

        return Storage::disk('public')->download($merchant->qr_code_path, 'qrcode_' . $merchant->business_name . '.png');
    }
}
