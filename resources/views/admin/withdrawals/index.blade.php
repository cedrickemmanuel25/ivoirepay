@extends('layouts.admin')

@section('title', 'Retraits')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes de Retrait</h1>
        <p class="text-sm text-gray-500 mt-1">Gérez les demandes de retrait des commerçants</p>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- En attente -->
    <div class="bg-white rounded-2xl shadow-sm border border-orange-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">En attente</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">{{ $pendingCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                <i class="fa-solid fa-hourglass-half text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Traitement -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">En traitement</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $processingCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-arrows-rotate text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Complétés -->
    <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Complétés</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $completedCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-check-double text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Montant total du mois -->
    <div class="bg-white rounded-2xl shadow-sm border border-primary/20 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Montant payé (Mois)</p>
                <p class="text-2xl font-bold text-primary mt-2">{{ number_format($totalMonthAmount, 0, ',', ' ') }} <span class="text-sm">FCFA</span></p>
            </div>
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 p-4">
    <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full relative">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Commerçant</label>
            <div class="absolute inset-y-0 left-0 top-5 pl-3 flex items-center pointer-events-none">
                <i class="fa-solid fa-search text-gray-400"></i>
            </div>
            <input type="text" name="merchant" value="{{ request('merchant') }}" placeholder="Nom, entreprise..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
        </div>
        
        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En traitement</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué / Annulé</option>
            </select>
        </div>

        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
        </div>

        <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
            <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors font-medium text-sm text-center">
                Filtrer
            </button>
            @if(request()->hasAny(['merchant', 'status', 'date']))
                <a href="{{ route('admin.withdrawals.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="withdrawalManager()">
    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity class="fixed bottom-4 right-4 z-50 rounded-lg shadow-xl p-4 flex items-center gap-3 border" :class="toast.type === 'success' ? 'bg-green-50 justify-between border border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" style="display: none;">
        <div>
            <i class="fa-solid" :class="toast.type === 'success' ? 'fa-check-circle text-green-500' : 'fa-triangle-exclamation text-red-500'"></i>
            <span class="font-medium text-sm ml-2" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <!-- Confirm Modal -->
    <div x-show="modal.show" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative overflow-hidden" @click.away="closeModal()">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0" :class="modal.action === 'process' ? 'bg-blue-100 text-blue-600' : 'bg-red-100 text-red-600'">
                    <i class="fa-solid" :class="modal.action === 'process' ? 'fa-money-bill-transfer' : 'fa-ban'"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="modal.title"></h3>
                    <p class="text-sm text-gray-500" x-text="modal.description"></p>
                </div>
            </div>

            <div x-show="modal.action === 'cancel'" class="mt-4 mb-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Motif d'annulation (envoyé au commerçant)</label>
                <input type="text" x-model="modal.reason" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none" placeholder="Ex: Informations bancaires incorrectes">
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <button @click="closeModal()" class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors text-gray-600 hover:bg-gray-100" :disabled="isLoading">Annuler</button>
                <button @click="confirmAction()" class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors text-white flex items-center gap-2" :class="modal.action === 'process' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700'" :disabled="isLoading">
                    <i class="fa-solid fa-spinner fa-spin" x-show="isLoading" style="display: none;"></i>
                    <span x-text="modal.confirmText"></span>
                </button>
            </div>
        </div>
    </div>


    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">ID / Date</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Commerçant</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Montant</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Compte Dépôt</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-50/50 transition-colors" id="row-{{ $withdrawal->id }}">
                        <td class="px-6 py-4">
                            <span class="block text-sm font-semibold text-gray-900">#{{ $withdrawal->id }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">{{ $withdrawal->created_at->format('d M Y, H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs shrink-0">
                                    {{ substr($withdrawal->merchant->business_name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <span class="block text-sm font-semibold text-gray-900">{{ $withdrawal->merchant->business_name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $withdrawal->merchant->user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-900 text-sm">{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-medium border border-gray-200">
                                @if(strtolower($withdrawal->wallet_type) === 'wave')
                                    <i class="fa-solid fa-water text-blue-500"></i> Wave
                                @elseif(strtolower($withdrawal->wallet_type) === 'mtn')
                                    <i class="fa-solid fa-signal text-yellow-500"></i> MTN
                                @elseif(strtolower($withdrawal->wallet_type) === 'moov')
                                    <i class="fa-solid fa-m text-blue-800"></i> Moov
                                @elseif(strtolower($withdrawal->wallet_type) === 'orange')
                                    <i class="fa-solid fa-o text-orange-500"></i> Orange
                                @else
                                    <i class="fa-solid fa-wallet text-gray-400"></i> {{ ucfirst($withdrawal->wallet_type) }}
                                @endif
                            </span>
                            <span class="block text-xs text-gray-500 mt-1 font-mono tracking-wider">{{ $withdrawal->wallet_number }}</span>
                        </td>
                        <td class="px-6 py-4" id="status-col-{{ $withdrawal->id }}">
                            @if($withdrawal->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-medium border border-orange-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> En attente
                                </span>
                            @elseif($withdrawal->status === 'processing')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Traitement
                                </span>
                            @elseif($withdrawal->status === 'completed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-medium border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Complété
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-medium border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Échoué / Annulé
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($withdrawal->status === 'pending')
                                <div class="flex items-center justify-end gap-2" id="actions-col-{{ $withdrawal->id }}">
                                    <!-- Traiter Button -->
                                    <button @click="openModal('process', {{ $withdrawal->id }}, '{{ $withdrawal->merchant->business_name }}', {{ escapeshellarg(number_format($withdrawal->amount, 0, ',', ' ')) }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors tooltip relative group" title="Lancer le traitement">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Traiter</span>
                                    </button>
                                    
                                    <!-- Annuler Button -->
                                    <button @click="openModal('cancel', {{ $withdrawal->id }}, '{{ $withdrawal->merchant->business_name }}', {{ escapeshellarg(number_format($withdrawal->amount, 0, ',', ' ')) }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors tooltip relative group" title="Refuser et annuler">
                                        <i class="fa-solid fa-ban"></i>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Annuler</span>
                                    </button>
                                </div>
                            @elseif($withdrawal->status === 'completed')
                                <span class="text-xs text-gray-400">Terminé le {{ $withdrawal->processed_at ? $withdrawal->processed_at->format('d/m/Y') : '' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-receipt text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-sm font-medium">Aucune demande de retrait trouvée</p>
                                <p class="text-xs text-gray-400 mt-1">Ajustez vos filtres ou réessayez plus tard.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($withdrawals->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $withdrawals->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('withdrawalManager', () => ({
            toast: { show: false, message: '', type: 'success' },
            modal: { show: false, action: '', id: null, title: '', description: '', confirmText: '', reason: '' },
            isLoading: false,

            openModal(action, id, merchantName, amount) {
                this.modal.action = action;
                this.modal.id = id;
                this.modal.reason = ''; // reset
                
                if (action === 'process') {
                    this.modal.title = "Traiter le retrait";
                    this.modal.description = `Voulez-vous lancer le traitement du retrait de ${amount} FCFA pour ${merchantName} via l'API Yengapay ?`;
                    this.modal.confirmText = "Lancer le paiement";
                } else {
                    this.modal.title = "Annuler le retrait";
                    this.modal.description = `Voulez-vous vraiment annuler le retrait de ${amount} FCFA pour ${merchantName} et recréditer son compte ?`;
                    this.modal.confirmText = "Confirmer l'annulation";
                }
                
                this.modal.show = true;
            },

            closeModal() {
                if(this.isLoading) return;
                this.modal.show = false;
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 4000);
            },

            async confirmAction() {
                this.isLoading = true;
                const id = this.modal.id;
                const action = this.modal.action;
                
                const url = action === 'process' 
                    ? `/admin/withdrawals/${id}/process` 
                    : `/admin/withdrawals/${id}/cancel`;

                const payload = {
                    _token: '{{ csrf_token() }}'
                };

                if (action === 'cancel' && this.modal.reason.trim() !== '') {
                    payload.reason = this.modal.reason.trim();
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Une erreur est survenue');
                    }

                    this.showToast(data.message, 'success');
                    
                    // Update UI optimistically
                    const statusCol = document.getElementById(`status-col-${id}`);
                    const actionsCol = document.getElementById(`actions-col-${id}`);
                    
                    if (statusCol && action === 'process') {
                        statusCol.innerHTML = `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Traitement
                            </span>
                        `;
                    } else if (statusCol && action === 'cancel') {
                        statusCol.innerHTML = `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-medium border border-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Échoué / Annulé
                            </span>
                        `;
                    }

                    if (actionsCol) {
                        actionsCol.style.opacity = '0';
                        setTimeout(() => actionsCol.remove(), 300);
                    }

                    this.closeModal();

                } catch (error) {
                    this.showToast(error.message, 'error');
                } finally {
                    this.isLoading = false;
                    this.closeModal();
                }
            }
        }));
    });
</script>
@endpush
