@extends('layouts.admin')

@section('title', 'Profil Utilisateur: ' . $user->name)

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-primary transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profil Utilisateur</h1>
            <p class="text-sm text-gray-500 mt-1">Détails et historique du compte de {{ $user->name }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <!-- Toggle Status Button -->
        <button 
            type="button" 
            onclick="document.getElementById('toggle-modal').style.display = 'flex'"
            class="px-4 py-2 rounded-lg font-medium text-sm transition-colors border {{ $user->is_active ? 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' : 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' }}">
            <i class="fa-solid {{ $user->is_active ? 'fa-user-lock' : 'fa-user-check' }} mr-1.5"></i>
            {{ $user->is_active ? 'Suspendre' : 'Réactiver' }}
        </button>

        <!-- Send SMS Button -->
        <button 
            type="button" 
            onclick="document.getElementById('sms-modal').style.display = 'flex'"
            class="px-4 py-2 rounded-lg font-medium text-sm transition-colors bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100">
            <i class="fa-solid fa-comment-sms mr-1.5"></i> SMS
        </button>

        <!-- Delete Button -->
        <button 
            type="button" 
            onclick="document.getElementById('delete-modal').style.display = 'flex'"
            class="px-4 py-2 rounded-lg font-medium text-sm transition-colors bg-red-600 text-white hover:bg-red-700 shadow-sm shadow-red-200">
            <i class="fa-solid fa-trash mr-1.5"></i> Supprimer
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-3">
        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('error') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Profile Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-8 border-b border-gray-100 flex flex-col items-center text-center">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-full object-cover shadow-sm mb-4 ring-4 ring-gray-50">
                @else
                    <div class="h-24 w-24 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-3xl shadow-sm mb-4 ring-4 ring-gray-50">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $user->name }}</h2>
                
                <div class="flex items-center gap-2 mb-4">
                    @if($user->role === 'client')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Client
                        </span>
                    @elseif($user->role === 'merchant')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Commerçant
                        </span>
                    @elseif($user->role === 'admin')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            Administrateur
                        </span>
                    @endif
                    
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Actif</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactif</span>
                    @endif
                </div>

                <div class="w-full mt-4 space-y-3 text-left">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-phone w-6 text-gray-400"></i>
                        <span class="font-medium text-gray-900">{{ $user->phone ?? 'Non spécifié' }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-solid fa-envelope w-6 text-gray-400"></i>
                        <span class="font-medium text-gray-900">{{ $user->email ?? 'Non spécifié' }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fa-regular fa-calendar text-gray-400 w-6"></i>
                        <span>Inscrit le <span class="font-medium text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</span></span>
                    </div>
                    <!-- Display Merchant Details if applicable -->
                    @if($user->role === 'merchant' && $user->merchant)
                        <div class="pt-4 mt-4 border-t border-gray-100">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Enterprise Infos</h4>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fa-solid fa-store w-6 text-gray-400"></i>
                                <span class="font-medium text-gray-900">{{ $user->merchant->business_name }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fa-solid fa-id-card w-6 text-gray-400"></i>
                                <span class="font-medium text-gray-900">{{ $user->merchant->rccm ?? 'Non fourni' }}</span>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.merchants.show', $user->merchant->id) }}" class="text-sm font-semibold text-primary hover:underline">Voir profil commerçant &rarr;</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats & History -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Stats Widgets -->
        @if($user->role === 'client')
            <h3 class="text-lg font-bold text-gray-900 mb-4">Statistiques Client</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b border-gray-100 pb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Transactions effectuées</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $clientStats['tx_count'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Montant total payé</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($clientStats['total_paid'] ?? 0, 0, ',', ' ') }} <span class="text-sm text-gray-500 font-normal">FCFA</span></p>
                    </div>
                </div>
            </div>
        @elseif($user->role === 'merchant' && $user->merchant)
            <h3 class="text-lg font-bold text-gray-900 mb-4">Statistiques Commerçant</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-gray-100 pb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 mb-1">Encaissements</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $merchantStats['tx_count'] ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 mb-1">Revenus totaux</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($merchantStats['total_revenue'] ?? 0, 0, ',', ' ') }} <span class="text-sm text-green-600/70 font-normal">FCFA</span></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-primary/20 p-5 flex flex-col justify-center bg-primary/5">
                    <p class="text-sm font-medium text-primary/80 mb-1">Solde Actuel</p>
                    <p class="text-2xl font-bold text-primary">{{ number_format($merchantStats['balance'] ?? 0, 0, ',', ' ') }} <span class="text-sm text-primary/70 font-normal">FCFA</span></p>
                </div>
            </div>
        @else
            <!-- Admin or user with no stats -->
            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 text-center text-gray-500 text-sm mb-8">
                Aucune statistique disponible pour cet utilisateur.
            </div>
        @endif

        <!-- Recent Transactions -->
        @if(in_array($user->role, ['client', 'merchant']))
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">10 dernières transactions</h3>
                </div>
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    {{ $user->role === 'client' ? 'Commerçant' : 'Client' }}
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="block text-sm font-semibold text-gray-900">{{ $tx->created_at->format('d M Y') }}</span>
                                        <span class="block text-xs text-gray-500">{{ $tx->created_at->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->role === 'client')
                                            <span class="block text-sm font-semibold text-gray-900">{{ $tx->merchant->business_name }}</span>
                                        @else
                                            <span class="block text-sm font-semibold text-gray-900">{{ $tx->client->name ?? 'Anonyme' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900">{{ number_format($tx->amount, 0, ',', ' ') }} FCFA</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($tx->status === 'success')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Succès</span>
                                        @elseif($tx->status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">En attente</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Échoué</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        Aucune transaction récente à afficher.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Modal Toggle Status -->
<div id="toggle-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative overflow-hidden text-center">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 {{ $user->is_active ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600' }}">
            <i class="fa-solid text-2xl {{ $user->is_active ? 'fa-user-lock' : 'fa-user-check' }}"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">
            {{ $user->is_active ? 'Suspendre' : 'Réactiver' }} ce compte ?
        </h3>
        <p class="text-gray-500 text-sm mb-6">
            @if($user->is_active)
                L'utilisateur ne pourra plus se connecter ni effectuer d'actions sur la plateforme.
            @else
                L'utilisateur aura à nouveau accès à toutes les fonctionnalités de son compte.
            @endif
        </p>
        
        <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="flex gap-3 justify-center">
            @csrf
            <!-- We aren't using AJAX here as the prompt said modal confirmation, standard form submit works well inside show view, or we can use the same logic as index. Let's use form to refresh the whole profile easily -->
            <button type="button" onclick="document.getElementById('toggle-modal').style.display = 'none'" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-semibold transition-colors">Annuler</button>
            <button type="submit" class="flex-1 px-4 py-2 text-white rounded-lg font-semibold transition-colors shadow-sm {{ $user->is_active ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600' }}">Confirmer</button>
        </form>
    </div>
</div>

<!-- Modal Send SMS -->
<div id="sms-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-comment-sms"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Envoyer un SMS personnalisé</h3>
        </div>
        
        <form action="{{ route('admin.users.sms', $user->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-gray-700 font-medium mb-1">Destinataire</label>
                <div class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 font-mono">
                    {{ $user->phone ?? 'Aucun numéro ! L\'envoi échouera.' }}
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm text-gray-700 font-medium mb-1">Message (Max 160 caractères)</label>
                <textarea name="message" rows="4" required maxlength="160" placeholder="Tapez votre message ici..." class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none resize-none"></textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('sms-modal').style.display = 'none'" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-semibold transition-colors">Annuler</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Account -->
<div id="delete-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative overflow-hidden text-center border-t-4 border-red-500">
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center bg-red-100 text-red-600 mb-4">
            <i class="fa-solid fa-trash-can text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Supprimer définitivement ?</h3>
        <p class="text-gray-500 text-sm mb-6">
            Cette action est <strong>irréversible</strong>. Toutes les données de l'utilisateur, y compris son profil commerçant éventuel, seront supprimées de la base de données.
        </p>
        
        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="flex gap-3 justify-center">
            @csrf
            @method('DELETE')
            <button type="button" onclick="document.getElementById('delete-modal').style.display = 'none'" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-semibold transition-colors">Annuler</button>
            <button type="submit" class="flex-1 px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg font-semibold transition-colors shadow-sm">Oui, suprimer</button>
        </form>
    </div>
</div>

@endsection
