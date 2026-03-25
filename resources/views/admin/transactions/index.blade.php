@extends('layouts.admin')

@section('title', 'Gestion des Transactions')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
        <p class="text-gray-500 mt-1">Consultez et exportez l'historique des paiements.</p>
    </div>
    
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.transactions.export.csv', request()->all()) }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-xl transition-colors flex items-center shadow-sm">
            <i class="fa-solid fa-file-csv mr-2 text-green-600"></i> Export CSV
        </a>
        <a href="{{ route('admin.transactions.export.pdf', request()->all()) }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-xl transition-colors flex items-center shadow-sm">
            <i class="fa-solid fa-file-pdf mr-2 text-red-600"></i> Export PDF
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500">Volume Total</p>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalAmount, 0, ',', ' ') }}</p>
                    <p class="ml-1 text-sm font-semibold text-gray-500">FCFA</p>
                </div>
            </div>
            <div class="p-3 bg-secondary/10 rounded-xl text-secondary">
                <i class="fa-solid fa-money-bill-transfer text-xl"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-secondary/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500">Transactions Réussies</p>
                <div class="mt-2">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($successCount, 0, ',', ' ') }}</p>
                </div>
            </div>
            <div class="p-3 bg-green-100 rounded-xl text-green-600">
                <i class="fa-solid fa-check text-xl"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-green-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500">Échecs & Annulées</p>
                <div class="mt-2">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($failedCount, 0, ',', ' ') }}</p>
                </div>
            </div>
            <div class="p-3 bg-red-100 rounded-xl text-red-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Commissions</p>
                <div class="mt-2 flex items-baseline">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCommission, 0, ',', ' ') }}</p>
                    <p class="ml-1 text-sm font-semibold text-gray-500">FCFA</p>
                </div>
            </div>
            <div class="p-3 bg-primary/10 rounded-xl text-primary">
                <i class="fa-solid fa-coins text-xl"></i>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
    </div>
</div>

<!-- Filtres -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 p-4">
    <form action="{{ route('admin.transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Date début</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Date fin</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Commerçant</label>
            <select name="merchant" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les marchands</option>
                @foreach($merchants as $merchant)
                    <option value="{{ $merchant->id }}" {{ request('merchant') == $merchant->id ? 'selected' : '' }}>
                        {{ $merchant->business_name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les statuts</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Réussie</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échouée</option>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Wallet</label>
            <select name="wallet" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les wallets</option>
                <option value="orange" {{ request('wallet') == 'orange' ? 'selected' : '' }}>Orange Money</option>
                <option value="mtn" {{ request('wallet') == 'mtn' ? 'selected' : '' }}>MTN MoMo</option>
                <option value="moov" {{ request('wallet') == 'moov' ? 'selected' : '' }}>Moov Money</option>
                <option value="wave" {{ request('wallet') == 'wave' ? 'selected' : '' }}>Wave</option>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Référence</label>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" name="ref" value="{{ request('ref') }}" placeholder="Réf..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white h-[38px]">
                </div>
                <button type="submit" class="w-10 h-[38px] bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors shadow-sm border border-primary flex items-center justify-center" title="Filtrer">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if(request()->anyFilled(['date_from', 'date_to', 'status', 'wallet', 'merchant', 'ref']))
                    <a href="{{ route('admin.transactions.index') }}" class="w-10 h-[38px] bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors border border-gray-200 flex items-center justify-center" title="Réinitialiser">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tableau Liste -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commerçant</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant (F)</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            <span title="{{ $tx->reference }}">{{ substr($tx->reference, 0, 8) }}...</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $tx->merchant->business_name ?? 'Inconnu' }}</div>
                            <div class="text-xs text-gray-500">{{ $tx->merchant->user->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $tx->client->name ?? 'Moi-même' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-right">
                            {{ number_format($tx->amount, 0, ',', ' ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $walletColors = [
                                    'orange' => 'bg-orange-100 text-orange-800',
                                    'mtn' => 'bg-yellow-100 text-yellow-800',
                                    'moov' => 'bg-blue-100 text-blue-800',
                                    'wave' => 'bg-cyan-100 text-cyan-800',
                                    'ivoirepay' => 'bg-primary/20 text-primary',
                                ];
                                $wColor = $walletColors[$tx->wallet_type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded {{ $wColor }}">
                                {{ ucfirst($tx->wallet_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($tx->status === 'success')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check mr-1 mt-0.5"></i> Succès
                                </span>
                            @elseif($tx->status === 'pending')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    <i class="fa-solid fa-clock-rotate-left mr-1 mt-0.5"></i> En attente
                                </span>
                            @else
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fa-solid fa-xmark mr-1 mt-0.5"></i> Échec
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                            {{ number_format($tx->commission_amount ?? 0, 0, ',', ' ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $tx->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-solid fa-receipt text-3xl mb-3 block text-gray-300"></i>
                            Aucune transaction trouvée avec les critères actuels.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
