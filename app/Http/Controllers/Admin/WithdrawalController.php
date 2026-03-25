<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Jobs\ProcessWithdrawalJob;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        // KPIs
        $pendingCount = WithdrawalRequest::where('status', 'pending')->count();
        $processingCount = WithdrawalRequest::where('status', 'processing')->count();
        $completedCount = WithdrawalRequest::where('status', 'completed')->count();
        
        $totalMonthAmount = WithdrawalRequest::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Query
        $query = WithdrawalRequest::with(['merchant.user']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('merchant')) {
            $query->whereHas('merchant.user', function($q) use ($request) {
                // Filter by business name or user name
                $q->where('business_name', 'ilike', '%' . $request->merchant . '%')
                  ->orWhere('name', 'ilike', '%' . $request->merchant . '%');
            });
        }

        $withdrawals = $query->latest()->paginate(25)->withQueryString();

        return view('admin.withdrawals.index', compact(
            'withdrawals',
            'pendingCount',
            'processingCount',
            'completedCount',
            'totalMonthAmount'
        ));
    }

    public function process(Request $request, WithdrawalRequest $withdrawal)
    {
        // Adjust authorization if you use explicit policies/gates. Default is open since it's behind `auth:admin`.
        // Gate::authorize('process-withdrawals');

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce retrait ne peut pas être traité (statut: ' . $withdrawal->status . ')'
            ], 400);
        }

        ProcessWithdrawalJob::dispatch($withdrawal);

        return response()->json([
            'status' => 'processing',
            'message' => 'Traitement lancé avec succès'
        ]);
    }

    public function cancel(Request $request, WithdrawalRequest $withdrawal, NotificationService $notifications)
    {
        // Gate::authorize('process-withdrawals');

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce retrait ne peut pas être annulé (statut: ' . $withdrawal->status . ')'
            ], 400);
        }

        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update(['status' => 'failed']);
            
            // Refund the merchant's balance 
            $withdrawal->merchant()->increment('balance', $withdrawal->amount);
        });

        // Use custom reason if provided, or default reason
        $reason = $request->input('reason', 'Annulé par l\'administrateur.');
        
        // Custom notification to merchant (using in-app and SMS conceptually, though NotificationService doesn't have an exact method for cancelled withdrawals, we can use sendInApp directly)
        $amount = number_format($withdrawal->amount, 0, ',', ' ');
        $notifications->sendInApp(
            userId: $withdrawal->merchant->user_id,
            type: 'withdrawal_cancelled',
            title: 'Retrait annulé',
            message: "Votre demande de retrait de {$amount} FCFA a été annulée. Motif: {$reason}",
            data: ['withdrawal_id' => $withdrawal->id]
        );
        $notifications->sendSms(
            $withdrawal->merchant->user->phone, 
            "IvoirePay: Votre retrait de {$amount} FCFA a été annulé."
        );

        return response()->json([
            'status' => 'failed',
            'message' => 'Retrait annulé, solde reversé avec succès.'
        ]);
    }
}
