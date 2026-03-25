@extends('layouts.admin')

@section('title', 'Commerçants')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Commerçants</h1>
        <p class="text-gray-500 mt-1">Gérez les comptes des commerçants inscrits.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Filtres -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 p-4">
    <form action="{{ route('admin.merchants.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        
        <!-- Recherche Globale -->
        <div class="flex-1 w-full relative">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Recherche</label>
            <div class="absolute inset-y-0 left-0 top-5 pl-3 flex items-center pointer-events-none">
                <i class="fa-solid fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, Téléphone, Email, RCCM..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
        </div>
        
        <!-- Statut KYC -->
        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Statut KYC</label>
            <select name="kyc_status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('kyc_status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('kyc_status') == 'approved' ? 'selected' : '' }}>Approuvés</option>
                <option value="rejected" {{ request('kyc_status') == 'rejected' ? 'selected' : '' }}>Rejetés</option>
            </select>
        </div>
        
        <!-- Statut du Compte -->
        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Compte</label>
            <select name="is_active" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les états</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>

        <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
            <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors font-medium text-sm text-center border border-primary">
                Filtrer
            </button>
            @if(request()->anyFilled(['search', 'kyc_status', 'is_active']))
                <a href="{{ route('admin.merchants.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm flex items-center justify-center border border-gray-200" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="merchantList()">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo / Nom</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entreprise</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde (XOF)</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">KYC</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($merchants as $merchant)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Nom / Avatar -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                                        {{ substr($merchant->user->name ?? '?', 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $merchant->user->name ?? 'Inconnu' }}</div>
                                    <div class="text-sm text-gray-500">{{ $merchant->user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Entreprise -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $merchant->business_name }}</div>
                            <div class="text-xs text-gray-500">RCCM: {{ $merchant->rccm_number ?? 'N/A' }}</div>
                        </td>
                        
                        <!-- Téléphone -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $merchant->user->phone ?? 'N/A' }}
                        </td>
                        
                        <!-- Solde -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-right">
                            {{ number_format($merchant->balance, 0, ',', ' ') }}
                        </td>
                        
                        <!-- Badge KYC -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($merchant->kyc_status === 'pending')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    En attente
                                </span>
                            @elseif($merchant->kyc_status === 'approved')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approuvé
                                </span>
                            @else
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejeté
                                </span>
                            @endif
                        </td>
                        
                        <!-- Toggle Actif/Inactif -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button @click="toggleMerchant({{ $merchant->id }}, '{{ $merchant->user->is_active ? '1' : '0' }}', '{{ addslashes($merchant->business_name) }}')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                    :class="activeStatuses[{{ $merchant->id }}] ?? {{ $merchant->user->is_active ? 'true' : 'false' }} ? 'bg-green-500' : 'bg-gray-300'"
                                    role="switch" aria-checked="true">
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                      :class="activeStatuses[{{ $merchant->id }}] ?? {{ $merchant->user->is_active ? 'true' : 'false' }} ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.merchants.show', $merchant->id) }}" class="text-primary hover:text-secondary font-semibold bg-primary/10 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                Profil
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-solid fa-store-slash text-3xl mb-3 block text-gray-300"></i>
                            Aucun commerçant trouvé pour ces critères.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($merchants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $merchants->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('merchantList', () => ({
            activeStatuses: {},
            
            async toggleMerchant(id, currentStateStr, name) {
                // Initialize if not present
                if (this.activeStatuses[id] === undefined) {
                    this.activeStatuses[id] = currentStateStr === '1';
                }
                
                const currentState = this.activeStatuses[id];
                const action = currentState ? 'désactiver' : 'activer';
                
                if (!confirm(`Êtes-vous sûr de vouloir ${action} le compte de ${name} ?`)) {
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/merchants/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.activeStatuses[id] = data.is_active;
                        // Optional: Show a small toast notification here
                    } else {
                        alert("Une erreur est survenue lors de la mise à jour.");
                    }
                } catch (error) {
                    console.error("Error toggling merchant status", error);
                    alert("Erreur de connexion.");
                }
            }
        }))
    })
</script>
@endpush
@endsection
