@extends('layouts.admin')

@section('title', 'Gestion KYC')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Validations KYC</h1>
        <p class="text-gray-500 mt-1">Gérez les dossiers de vérification d'identité des commerçants.</p>
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

<!-- Filtre de recherche -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 p-4">
    <form action="{{ route('admin.kyc.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <input type="hidden" name="status" value="{{ $status }}">
        
        <div class="flex-1 w-full relative">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Rechercher un dossier</label>
            <div class="absolute inset-y-0 left-0 top-5 pl-3 flex items-center pointer-events-none">
                <i class="fa-solid fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, entreprise, téléphone..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
        </div>

        <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
            <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors font-medium text-sm text-center border border-primary">
                Rechercher
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.kyc.index', ['status' => $status]) }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm flex items-center justify-center border border-gray-200" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Onglets de filtre -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex overflow-x-auto" aria-label="Tabs">
            <a href="{{ route('admin.kyc.index') }}" 
               class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ !$status ? 'border-secondary text-secondary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Tous
            </a>
            
            <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}" 
               class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center {{ $status === 'pending' ? 'border-secondary text-secondary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                En attente
                @if($pendingKycCount > 0)
                    <span class="ml-2 bg-orange-100 text-orange-600 py-0.5 px-2.5 rounded-full text-xs font-bold">{{ $pendingKycCount }}</span>
                @endif
            </a>
            
            <a href="{{ route('admin.kyc.index', ['status' => 'approved']) }}" 
               class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $status === 'approved' ? 'border-secondary text-secondary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Approuvés
            </a>
            
            <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}" 
               class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $status === 'rejected' ? 'border-secondary text-secondary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Rejetés
            </a>
        </nav>
    </div>

    <!-- Tableau -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo / Nom</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entreprise</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soumission</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($merchants as $merchant)
                    <tr class="hover:bg-gray-50 transition-colors">
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $merchant->business_name }}</div>
                            <div class="text-xs text-gray-500">RCCM: {{ $merchant->rccm_number ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $merchant->user->phone ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $merchant->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($merchant->kyc_status === 'pending')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    <i class="fa-solid fa-clock mr-1 mt-0.5"></i> En attente
                                </span>
                            @elseif($merchant->kyc_status === 'approved')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check-circle mr-1 mt-0.5"></i> Approuvé
                                </span>
                            @else
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fa-solid fa-times-circle mr-1 mt-0.5"></i> Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.kyc.show', $merchant->id) }}" class="text-primary hover:text-secondary font-semibold bg-primary/10 px-3 py-1.5 rounded-lg transition-colors">
                                Voir dossier
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                            <i class="fa-regular fa-folder-open text-3xl mb-3 block text-gray-300"></i>
                            Aucun dossier KYC trouvé.
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
@endsection
