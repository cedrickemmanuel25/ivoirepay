<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Services\AfricasTalkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                  ->orWhere('phone', 'ilike', '%' . $search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active' ? true : false;
            $query->where('is_active', $isActive);
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with('merchant')->findOrFail($id);
        
        $clientStats = [];
        $merchantStats = [];
        $recentTransactions = collect();

        if ($user->role === 'client') {
            $clientStats = [
                'tx_count' => $user->clientTransactions()->count(),
                'total_paid' => $user->clientTransactions()->successful()->sum('amount')
            ];
            $recentTransactions = $user->clientTransactions()
                ->with('merchant')
                ->latest()
                ->take(10)
                ->get();
        } elseif ($user->role === 'merchant' && $user->merchant) {
            $merchantStats = [
                'tx_count' => $user->merchant->transactions()->count(),
                'total_revenue' => $user->merchant->transactions()->successful()->sum('amount'), // Not subtracting commission here, as per requirements "revenus totaux" usually means received gross or net. We'll show gross.
                'balance' => $user->merchant->balance
            ];
            $recentTransactions = $user->merchant->transactions()
                ->with('client')
                ->latest()
                ->take(10)
                ->get();
        }

        return view('admin.users.show', compact('user', 'clientStats', 'merchantStats', 'recentTransactions'));
    }

    public function toggleActive(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Don't allow admins to disable themselves easily
        if ($user->id === auth()->id()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous ne pouvez pas suspendre votre propre compte.'
                ], 403);
            }
            return back()->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'réactivé' : 'suspendu';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'is_active' => $user->is_active,
                'message' => "Le compte utilisateur a été $action avec succès."
            ]);
        }

        return back()->with('success', "Le compte utilisateur a été $action avec succès.");
    }

    public function sendSms(Request $request, $id, AfricasTalkingService $smsService)
    {
        $request->validate([
            'message' => 'required|string|max:160'
        ]);

        $user = User::findOrFail($id);
        
        if (!$user->phone) {
            return back()->with('error', 'Cet utilisateur n\'a pas de numéro de téléphone enregistré.');
        }

        try {
            $smsService->sendMessage($user->phone, $request->message);
            return back()->with('success', 'SMS envoyé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        DB::transaction(function () use ($user) {
            if ($user->merchant) {
                $user->merchant()->delete();
            }
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'Compte utilisateur supprimé avec succès.');
    }
}
