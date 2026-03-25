<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        // Calculate KPIs
        $kpiQuery = clone $query;
        $totalAmount = $kpiQuery->sum('amount');
        
        $kpiQuery = clone $query;
        $successCount = $kpiQuery->where('status', 'success')->count();
        
        $kpiQuery = clone $query;
        $failedCount = $kpiQuery->where('status', 'failed')->count();
        
        $kpiQuery = clone $query;
        $totalCommission = $kpiQuery->sum('commission_amount');

        // Paginate results
        $transactions = $query->paginate(25)->withQueryString();

        // For filter dropdowns
        $merchants = Merchant::all();

        return view('admin.transactions.index', compact(
            'transactions', 
            'totalAmount', 
            'successCount', 
            'failedCount', 
            'totalCommission',
            'merchants'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->get();

        $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions'));
        return $pdf->download('transactions_' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $transactions = $query->get();

        return Excel::download(new TransactionsExport($transactions), 'transactions_' . date('Y-m-d') . '.csv');
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Transaction::with(['merchant.user', 'client'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('wallet')) {
            $query->where('wallet_type', $request->wallet);
        }
        
        if ($request->filled('merchant')) {
            $query->where('merchant_id', $request->merchant);
        }
        
        if ($request->filled('ref')) {
            $query->where('reference', 'like', '%' . $request->ref . '%');
        }

        return $query;
    }
}
