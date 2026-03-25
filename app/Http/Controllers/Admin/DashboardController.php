<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            // KPIs
            'todayTransactionAmount' => Transaction::today()->successful()->sum('amount'),
            'todayTransactionCount'  => Transaction::today()->successful()->count(),
            'activeMerchantsCount'   => Merchant::where('kyc_status', 'approved')->count(),
            'pendingKycCount'        => Merchant::where('kyc_status', 'pending')->count(),
            'monthlyRevenue'         => Transaction::thisMonth()->successful()->sum('commission_amount'),
            
            // Graphique 7 jours
            'weeklyData' => Transaction::successful()
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            // Répartition wallets
            'walletDistribution' => Transaction::successful()
                ->selectRaw('wallet_type, COUNT(*) as count')
                ->groupBy('wallet_type')
                ->get(),
            
            // Transactions récentes
            'recentTransactions' => Transaction::with(['merchant.user', 'client'])
                ->latest()
                ->limit(10)
                ->get()
        ];

        return view('admin.dashboard', $data);
    }
}
