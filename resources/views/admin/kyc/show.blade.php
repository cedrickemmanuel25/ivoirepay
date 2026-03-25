@extends('layouts.admin')

@section('title', 'Détail Dossier KYC')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.kyc.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-primary hover:border-primary transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dossier de {{ $merchant->business_name }}</h1>
            <p class="text-gray-500 mt-1 text-sm">Soumis le {{ $merchant->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
    
    <!-- Status Badge -->
    <div>
        @if($merchant->kyc_status === 'pending')
            <span class="px-4 py-2 rounded-full bg-orange-100 text-orange-800 font-bold text-sm flex items-center">
                <i class="fa-solid fa-clock mr-2"></i> En attente de validation
            </span>
        @elseif($merchant->kyc_status === 'approved')
            <span class="px-4 py-2 rounded-full bg-green-100 text-green-800 font-bold text-sm flex items-center">
                <i class="fa-solid fa-check-circle mr-2"></i> Approuvé le {{ $merchant->approved_at ? $merchant->approved_at->format('d/m/Y') : 'N/A' }}
            </span>
        @else
            <span class="px-4 py-2 rounded-full bg-red-100 text-red-800 font-bold text-sm flex items-center">
                <i class="fa-solid fa-times-circle mr-2"></i> Rejeté
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

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6" x-data="{ documentModalOpen: false, currentImageUrl: '', currentImageTitle: '' }">
    <!-- Colonne Gauche (60%) : Informations -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Informations du Commerçant -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-user text-primary mr-2"></i> Infos du Gérant
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase">Nom complet</span>
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
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase">Numéro CNI</span>
                    <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->cni_number ?? 'Non renseigné' }}</span>
                </div>
            </div>
        </div>

        <!-- Informations de l'Entreprise -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-store text-primary mr-2"></i> Infos de l'Entreprise
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <span class="block text-xs font-medium text-gray-500 uppercase">Nom de l'entreprise</span>
                    <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->business_name }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="block text-xs font-medium text-gray-500 uppercase">Adresse</span>
                    <span class="block mt-1 text-sm text-gray-900">{{ $merchant->business_address ?? 'Non renseignée' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-gray-500 uppercase">Numéro RCCM</span>
                    <span class="block mt-1 text-sm font-semibold text-gray-900">{{ $merchant->rccm_number ?? 'Non renseigné' }}</span>
                </div>
            </div>
        </div>

        @if($merchant->kyc_status === 'rejected')
            <!-- Motif de rejet -->
            <div class="bg-red-50 rounded-2xl border border-red-200 p-6">
                <h2 class="text-lg font-bold text-red-900 mb-2 flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Motif du rejet
                </h2>
                <p class="text-red-800 text-sm bg-white p-4 rounded-lg border border-red-100">{{ $merchant->kyc_rejection_reason }}</p>
            </div>
        @endif

        @if($merchant->kyc_status === 'pending')
            <!-- Actions de validation (affichées uniquement si en attente) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ showRejectForm: false }">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-clipboard-check text-primary mr-2"></i> Décision de Validation
                </h2>
                
                <div class="flex gap-4" x-show="!showRejectForm">
                    <form action="{{ route('admin.kyc.approve', $merchant->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Êtes-vous sûr de vouloir approuver ce compte ? Un QR code sera généré et un SMS envoyé.');">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition-colors flex justify-center items-center">
                            <i class="fa-solid fa-check mr-2"></i> Approuver le dossier
                        </button>
                    </form>
                    
                    <button @click="showRejectForm = true" type="button" class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 font-bold py-3 px-4 rounded-xl transition-colors flex justify-center items-center">
                        <i class="fa-solid fa-xmark mr-2"></i> Rejeter
                    </button>
                </div>

                <!-- Formulaire de rejet -->
                <div x-show="showRejectForm" x-cloak class="mt-4 p-4 border border-red-200 bg-red-50 rounded-xl">
                    <h3 class="font-bold text-red-800 mb-2">Motif du rejet</h3>
                    <form action="{{ route('admin.kyc.reject', $merchant->id) }}" method="POST">
                        @csrf
                        <textarea name="reason" rows="3" required class="w-full border-red-300 rounded-lg shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 p-3 text-sm" placeholder="Veuillez expliquer pourquoi ce dossier est rejeté (sera envoyé par SMS au commerçant)..."></textarea>
                        
                        <div class="mt-3 flex justify-end gap-2">
                            <button type="button" @click="showRejectForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700">Confirmer le rejet</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Colonne Droite (40%) : Documents -->
    <div class="lg:col-span-2 space-y-4 bg-gray-50 rounded-2xl p-6 border border-gray-100">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <i class="fa-regular fa-folder-open text-primary mr-2"></i> Documents fournis
        </h2>

        @if(isset($merchant->kycDocuments) && $merchant->kycDocuments->count() > 0)
            <div class="space-y-4">
                @foreach($merchant->kycDocuments as $doc)
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm group cursor-pointer hover:border-secondary transition-colors"
                         @click="documentModalOpen = true; currentImageUrl = '{{ Storage::url($doc->file_path) }}'; currentImageTitle = '{{ ucfirst($doc->document_type) }}'">
                        
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-bold text-gray-800">{{ ucfirst($doc->document_type) }}</span>
                            <button type="button" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-primary hover:text-white transition-colors">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> Aperçu
                            </button>
                        </div>
                        
                        <div class="relative h-32 w-full bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                            @if(in_array(pathinfo($doc->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                <img src="{{ Storage::url($doc->file_path) }}" alt="{{ $doc->document_type }}" class="object-cover h-full w-full group-hover:scale-105 transition-transform duration-300">
                            @else
                                <i class="fa-solid fa-file-pdf text-4xl text-red-400"></i>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fa-solid fa-folder-minus text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 text-sm">Aucun document téléchargé.</p>
            </div>
        @endif
    </div>
    
    <!-- Modal Aperçu Document -->
    <div x-show="documentModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="documentModalOpen = false"></div>

        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-4xl m-4 z-[101] overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <h3 class="text-lg font-bold text-gray-900" x-text="currentImageTitle">Aperçu du document</h3>
                <button type="button" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none" @click="documentModalOpen = false">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto bg-gray-100 flex-1 flex items-center justify-center">
                <img :src="currentImageUrl" class="max-w-full max-h-full object-contain rounded shadow-sm border border-gray-200" alt="Document Preview">
            </div>
            
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-right">
                <a :href="currentImageUrl" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-2 text-xs"></i> Ouvrir dans un nouvel onglet
                </a>
                <button type="button" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900" @click="documentModalOpen = false">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
