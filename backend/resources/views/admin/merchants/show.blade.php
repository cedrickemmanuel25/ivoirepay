@extends('layouts.admin')

@section('title', 'Profil Commerçant')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.merchants.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-primary hover:border-primary transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profil de {{ $merchant->business_name }}</h1>
            <p class="text-gray-500 mt-1 text-sm">Gestion du compte commerçant</p>
        </div>
    </div>
    
    <div>
        @if($merchant->user->is_active)
            <span class="px-4 py-2 rounded-full bg-green-100 text-green-800 font-bold text-sm inline-flex items-center">
                <i class="fa-solid fa-circle-check mr-2"></i> Compte Actif
            </span>
        @else
            <span class="px-4 py-2 rounded-full bg-gray-100 text-gray-800 font-bold text-sm inline-flex items-center">
                <i class="fa-solid fa-circle-minus mr-2"></i> Compte Inactif
            </span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Top Section: Stats & Info -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <!-- Balance Card (Large Green) -->
    <div class="bg-primary rounded-2xl shadow-lg border border-primary/20 p-8 text-white relative overflow-hidden flex flex-col justify-center">
        <div class="relative z-10">
            <h2 class="text-primary-100 font-medium text-lg opacity-80 mb-2">Solde Actuel (XOF)</h2>
            <div class="text-5xl font-black tracking-tight mb-4">
                {{ number_format($merchant->balance, 0, ',', ' ') }}
            </div>
            
            <div class="flex items-center space-x-4 text-sm mt-4">
                <div class="bg-white/10 px-3 py-1.5 rounded-lg inline-flex items-center backdrop-blur-sm">
                    <i class="fa-solid fa-money-bill-transfer mr-2"></i> Retraits: {{ number_format($totalWithdrawals, 0, ',', ' ') }} F
                </div>
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-white opacity-5 rounded-full"></div>
        <div class="absolute -right-2 -top-10 w-24 h-24 bg-secondary opacity-10 rounded-full"></div>
        <i class="fa-solid fa-wallet absolute right-6 top-6 text-6xl opacity-10"></i>
    </div>
    
    <!-- Merchant Info -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fa-solid fa-address-card text-primary mr-2"></i> Informations
            </h2>
            <a href="{{ route('admin.kyc.show', $merchant->id) }}" class="text-sm text-secondary hover:text-primary transition-colors font-medium">
                Voir détails KYC <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase">Gérant</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->user->name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase">Téléphone</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->user->phone ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase">Email</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->user->email ?? 'N/A' }}</span>
            </div>
            <div class="md:col-span-2">
                <span class="block text-xs font-medium text-gray-500 uppercase">Adresse Entreprise</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->business_address ?? 'Non spécifiée' }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase">N° RCCM</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->rccm_number ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase">N° CNI</span>
                <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->cni_number ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <!-- QR Code Section -->
    <div class="lg:col-span-1 border border-gray-100 rounded-2xl p-6 bg-white shadow-sm flex flex-col items-center justify-center">
        <h2 class="text-sm font-bold text-gray-900 mb-6 text-center">QR Code de Paiement</h2>
        
        @if($merchant->kyc_status === 'approved' && $merchant->qr_code_path)
            <div class="bg-white p-2 border border-gray-200 rounded-xl shadow-sm mb-6 inline-block">
                <img src="{{ Storage::url($merchant->qr_code_path) }}" alt="QR Code" class="w-[200px] h-[200px] object-contain">
            </div>
            
            <a href="{{ route('admin.merchants.qr', $merchant->id) }}" class="w-full flex justify-center items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-xl transition-colors">
                <i class="fa-solid fa-download mr-2"></i> Télécharger PNG
            </a>
        @else
            <div class="w-[200px] h-[200px] bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center mb-6">
                <div class="text-center text-gray-400">
                    <i class="fa-solid fa-qrcode text-4xl mb-2"></i>
                    <p class="text-xs">QR Non Disponible</p>
                    <p class="text-[10px] mt-1">(KYC requis)</p>
                </div>
            </div>
            <button disabled class="w-full flex justify-center items-center px-4 py-2 bg-gray-100 text-gray-400 text-sm font-bold rounded-xl cursor-not-allowed">
                <i class="fa-solid fa-download mr-2"></i> Télécharger PNG
            </button>
        @endif
    </div>

    <!-- Transactions List Section -->
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900">
                <i class="fa-solid fa-list-ul text-primary mr-2"></i> Dernières Transactions (20)
            </h2>
            <a href="{{ route('admin.transactions.index', ['merchant' => $merchant->id]) }}" class="text-sm text-primary hover:text-secondary transition-colors font-semibold">
                Voir tout l'historique
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant (F)</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                {{ substr($tx->reference, 0, 8) }}...
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $tx->client->name ?? 'Inconnu' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-right">
                                +{{ number_format($tx->amount, 0, ',', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-gray-100 text-gray-800">
                                    {{ ucfirst($tx->wallet_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($tx->status === 'success')
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block" title="Succès"></span>
                                @elseif($tx->status === 'pending')
                                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block" title="En attente"></span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block" title="Échec"></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                {{ $tx->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-gray-500 text-sm">
                                Aucune transaction récente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination pour les transactions (limité via controller) -->
        @if($transactions->hasPages())
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
